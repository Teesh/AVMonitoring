# So I used python because getting all the modules setup for pings and requests and timezones seemed easier in python
# But it could probably be easily translated to PHP but it doesn't have to be because it is independant of the website.  I haven't looked at which language is faster in terms of runtime
# cronjob runs this every 5 minutes because Lens doesn't update any faster than that.  The script itself takes 110 seconds=ish to run
import mysql.connector
import requests
import json
from requests.auth import HTTPBasicAuth
import time
import subprocess
import datetime
import pytz

start_time = time.time()

#database connection
try:
    conn=mysql.connector.connect(user='root',password='REMOVED',host='localhost',database='usmnet')
except mysql.connector.Error as err:
    print(err)

cursor = conn.cursor(buffered=True); # this line fixed a bug, I forget which bug
query_all = ("SELECT mac_address FROM physical_info")
cursor.execute(query_all)
mac_list = list(cursor.fetchall())
macs = [item for sublist in mac_list for item in sublist]
query_in = ("INSERT INTO network_info (mac_address,ip_address,subnet_name,subnet,switch,port,jack,status) VALUES (%s,%s,%s,%s,%s,%s,%s,%s) ON DUPLICATE KEY UPDATE timestamp = %s")
ouis = ['58fcdb','0005a6','00609f','00190b'] # most common OUIs
url = 'https://lens-api.cites.illinois.edu/lens/uiuc-lens/ip_mac?'
for oui in ouis:
    url += 'mac=~'+ oui + '&'
url += 'content-type=text/x-json&dressings=ipm_ports,mpt_interface'
#print(url)
response = requests.get(url,auth=('lens-portlet','REMOVED'),verify=True) # I had the password in here.  Again talk to REMOVED to get access to this account which has rean-only access to all networks in Lens
# probably making this script PHP would allow you to pull the password from the config file directly
data = json.loads(response.text)
macs_batch_list = []

# So the overall algorithm is as follows:
# Grab all the Lens data for the 4 or 5 most common OUIs because a batch query is faster than individual queries when you own most of the Extron devices across campus
# Search for your MAC addresses in the batch data you just got, if you find it, cross it off your list, take the data and put it into MySQL
# Throw the rest of the batch data away
# If anything is left on your list that isn't crossed off, do individual Lens queries for those and add the data to MySQL
#
# There are three queries we do for each MAC address to get all th: ip_mac, mac_port, interfact
# The interface data depends on what you find in the mac_port
# TODO: Add better exception handling. Theres a few things that are hinky because of how Lens handles data that isn't theres
# For example, sometimes Lens will have ip_mac data but not mac_port data for some unknown reason, and while it seems unintuitive, it was not a possibility I had put in an exception for, so the script broke trying to dereference non-existant variables

count = 0
for dev in data["result"]: # dev is the random device number for the ip_mac result
    ip_address = ''
    subnet_name = ''
    subnet = ''
    switch = ''
    port = ''
    jack = ''

    if data["objects"]["ip_mac"][dev]["mac"] in macs:
        #if(len(data['objects']['ip_mac'][dev]['ports']) != 0):
        mac_address = data["objects"]["ip_mac"][dev]["mac"]
        ip_address = data['objects']['ip_mac'][dev]['ip']
        subnet_name = data['objects']['ip_mac'][dev]['subnet_name']
        subnet = data['objects']['ip_mac'][dev]['subnet']
        try:
            dev2 = data['objects']['ip_mac'][dev]['ports'][0] # dev2 is the equivalent random device number for the mac_port result
        except:
            ip_address = ''
            subnet_name = ''
            subnet = ''
            switch = ''
            port = ''
            jack = ''
        if(dev2):
            switch = data['objects']['mac_port'][dev2]['device_name']
            port = data['objects']['mac_port'][dev2]['ifname']
            interface_id = data['objects']['mac_port'][dev2]['interface_id']
            try:
                jack = data['objects']['interface'][interface_id]['ifalias'] # interface_id is the random device number for the interface result
            except:
                pass
            if(jack == None):
                jack = ''
        if(ip_address != ''): # just check for nulls everywhere or put in try/excepts everywhere to clean this up
            test_4 = 4 # i tried to make the ping results more accurate by trying to account for false negatives, because I assume false positives in pings are much rarer
            while test_4:
                test_4 -= 1
                response = subprocess.Popen(['ping',ip_address,'-c','1','-W','2'],stdout=subprocess.PIPE)
                response.wait()
                if(response.poll()):
                    status = 'DOWN'
                else:
                    status = 'UP'
                    break
        else:
            status = 'OFF'
        last_seen = pytz.timezone("America/Chicago").localize(datetime.datetime.now()) # Keeping the timezone straight between the script and the database is important to have up-to-date data.  I accidentally had 6 hour old data showing on the UI once because one was on Greenwich time
        data_all = (mac_address,ip_address,subnet_name,subnet,switch,port,jack,status,last_seen)
        #print(data_all)
        cursor.execute(query_in,data_all)
        macs_batch_list.append(mac_address)
        count+=1
#print(str(count))
#print(str(len(macs)))
macs = [x for x in macs if x not in macs_batch_list]
# This is the same thing as above but for individual MACs
for mac_address in macs:
    ip_address = ''
    subnet_name = ''
    subnet = ''
    switch = ''
    port = ''
    jack = ''
    last_seen = pytz.timezone("America/Chicago").localize(datetime.datetime.now())
    if not any(oui in mac_address for oui in ouis):
        response = requests.get('https://lens-api.cites.illinois.edu/lens/uiuc-lens/ip_mac?mac=' + mac_address + '&content-type=text/x-json&dressings=ipm_ports,mpt_interface',
                                auth=('lens-portlet','Scissorsharpie2020'),verify=True)
        data = json.loads(response.text)
        for ip_mac in data['objects']:
            for dev in data['objects']['ip_mac']:
                ip_address = data['objects']['ip_mac'][dev]['ip']
                subnet_name = data['objects']['ip_mac'][dev]['subnet_name']
                subnet = data['objects']['ip_mac'][dev]['subnet']
        for mac_port in data['objects']:
            for dev in data['objects']['mac_port']:
                switch = data['objects']['mac_port'][dev]['device_name']
                port = data['objects']['mac_port'][dev]['ifname']
                interface_id = data['objects']['mac_port'][dev]['interface_id']
                try:
                    jack = data['objects']['interface'][interface_id]['ifalias']
                except:
                    pass
                if(jack == None):
                    jack = ''
    if(ip_address != ''):
        test_4 = 4
        while test_4:
            test_4 -= 1
            response = subprocess.Popen(['ping',ip_address,'-c','1','-W','2'],stdout=subprocess.PIPE)
            response.wait()
            if(response.poll()):
                status = 'DOWN'
            else:
                status = 'UP'
                break
    else:
        status = 'OFF'
    last_seen = pytz.timezone("America/Chicago").localize(datetime.datetime.now())
    data_all = (mac_address,ip_address,subnet_name,subnet,switch,port,jack,status,last_seen)
    #print(data_all)
    cursor.execute(query_in,data_all)
    count+=1

conn.commit() # if anything fails in the script, nothing gets changed in the database unless this line is reached successfully
cursor.close()
conn.close()

#print(str(count))
elapsed_time = time.time() - start_time # important to keep this under 5 minutes for obvious reasons.  The webserver and the cronjob servers should be separated so that there isn't a competition for reasources
print(elapsed_time)
