<!--
  A help guide for the unituitive parts of the application
-->
<div class="container-fluid">
  <h5>Search parameters</h5>
  <div class="row mb-2">
    <button type="button" class="btn btn-dark rounded-0 disabled">
      Lincoln Hall
    </button>
    <span class="p-2" style="color:white;font-size:16px">&nbsp;:&nbsp;Search term, includes internal spaces, strips external spaces, and full-text searches all fields, case-insensitive</span>
  </div>
  <div class="row mb-2">
    <button type="button" class="btn btn-dark rounded-0 disabled">
      Lincoln Hall | Altgeld | Solstice
    </button>
    <span class="p-2" style="color:white;font-size:16px">&nbsp;:&nbsp;Pipe | acts as an OR operator, searching for any piped terms in any field</span>
  </div>
  <div class="row mb-2">
    <button type="button" class="btn btn-dark rounded-0 disabled">
      Lincoln Hall, Altgeld, Solstice
    </button>
    <span class="p-2" style="color:white;font-size:16px">&nbsp;:&nbsp;Comma , acts as an AND operator, searching for all comma-separated terms in any field.  Search terms do not all need to appear in same field</span>
  </div>
  <div class="row mb-2">
    <button type="button" class="btn btn-dark rounded-0 disabled">
      Lincoln Hall, Altgeld | Solstice
    </button>
    <span class="p-2" style="color:white;font-size:16px">&nbsp;:&nbsp;Order of operations is OR first then AND, so this searches for Lincoln Hall AND (Altgeld OR Solstice)</span>
  </div>
  <div class="row mb-2">
    <button type="button" class="btn btn-dark rounded-0 disabled">
      location=Lincoln Hall, device_type=Solstice
    </button>
    <span class="p-2" style="color:white;font-size:16px">&nbsp;:&nbsp;Equal Sign = acts as a column name specifier, which must exactly match a column name from the database, found in the Database menu. Cannot be combined with OR operator</span>
  </div>
  <div class="row mb-2">
    <button type="button" class="btn btn-dark rounded-0 disabled">
      !Lincoln Hall, device_type!=Solstice
    </button>
    <span class="p-2" style="color:white;font-size:16px">&nbsp;:&nbsp;Exlamation point ! acts as a NOT operator. Can be used in front of a search term or an Equal Sign. Cannot be combined with OR operator</span>
  </div>
  <div class="row mb-2">
    <button type="button" class="btn btn-dark rounded-0 disabled">
      ^[a-d]
    </button>
    <span class="p-2" style="color:white;font-size:16px">&nbsp;:&nbsp;Theoretically also supports single MYSQL regular expression to search on all fields (Experimental)</span>
  </div>
</div>
