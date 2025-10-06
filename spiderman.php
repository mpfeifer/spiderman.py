<?php

session_start();

$databaseFile = 'spiderman.db';

try {
    if (!isset($_SESSION['logbook'])) {
        $_SESSION['logbook']=[];
        $_SESSION['logbook'][] = [time(), "Session started"];
    }
    if (!isset($_SESSION['hosts'])) {
        $_SESSION['logbook'][] = [time(), "Getting hosts from the database and storing hosts in session"];
        $db = new SQLite3($databaseFile);
        $results = $db->query('SELECT * FROM hosts');
        $_SESSION['hosts'] = [];
        while ($host = $results->fetchArray(SQLITE3_ASSOC)) {
            $_SESSION['hosts'][] = $host;
        }
        $db->close();
    }
} catch (Exception $e) {
    $_SESSION['logbook'][] = [time(), $e->getMessage()];
}

?>

<!DOCTYPE html>
<html lang="en">

  <head>
    <link rel="icon" href="./favicon.ico" type="image/x-icon">
    <link rel="icon" href="index.ico">
    <link rel="apple-touch-icon" href="index.png">
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="author" content="Matthias">
    <meta name="description" content="Just trying to connect to port 80 on random osts via ipv4.">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta property="og:description" content="Just trying to connect to port 80 on random osts via ipv4.">
    <meta property="og:image" content="index.png">
    <meta property="og:title" content="Search for random http server.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="add-url-here">
    <title>Spiderman-UI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <style>
      table {
          table-layout: fixed;
          width: 100%; /* Optional: set a width for the entire table */
      }

      @media only screen and (orientation:portrait) {
          .hide-on-mobile {
              display: none;
          }
      }

      @media only screen and (orientation:landscape) {

          th:nth-child(1), td:nth-child(1) {
              width: 3em; /* Specific width for the first column */
          }

          th:nth-child(2), td:nth-child(2) {
              width: 10em;
          }

          th:nth-child(4), td:nth-child(4) {
              width: 10em;
          }

      }
      
      tr {
          height: 2.5em;
      }

      td div {
          height: 2.5em; /* Adjust to fit within the row height */
          overflow: hidden; /* Hide overflowing content */
          white-space: normal; /* Allow text to wrap */
          text-overflow: ellipsis; /* Add ellipsis for hidden text */
      }
    </style>
  </head>

  <body>
    <script>
      var hosts = <?php echo json_encode($_SESSION['hosts'])?>;

      let prevPage=1;
      let nextPage=2;
      let hostsPerPage = 5;
      let currentPage=1;

      function gotoPage(page) {
          if (page != currentPage && ((page-1) * hostsPerPage) < hosts.length) {
            fillTable(page);
            currentPage = page;
            nextPage = currentPage+1;
            prevPage = Math.max(1, currentPage-1);
            }
            }

            function fillTable(pageIndex) {
            let from = (pageIndex-1) * hostsPerPage;
            let to = pageIndex * hostsPerPage;
            let data = hosts.slice(from, to);
            var tableBody = document.getElementById("hosts-table");
          tableBody.innerHTML = '';

          data.forEach(rowData => {
              var tr = document.createElement("tr");
              var td = document.createElement("td");
              var div = document.createElement("div");
              div.textContent = rowData['id'];
              td.appendChild(div);
              tr.appendChild(td);
              div = document.createElement("div");
              td = document.createElement("td");
              td.className='hide-on-mobile';
              const dateObject = new Date(rowData['timestamp']*1000);
              div.textContent = dateObject.toLocaleString();
              td.appendChild(div);
              tr.appendChild(td);
              div = document.createElement("div");
              td = document.createElement("td");
              td.className='hide-on-mobile';
              div.textContent = rowData['hostname'];
              td.appendChild(div);
              tr.appendChild(td);
              div = document.createElement("div");
              td = document.createElement("td");
              const newLink = document.createElement('a');
              newLink.href = `http://${rowData['ip']}`;
              newLink.textContent = rowData['ip'];
              newLink.target = '_blank'; // Optional: Opens the link in a new tab
              newLink.className = 'btn btn-primary w-100'; // Optional: Add Bootstrap classes
              div.appendChild(newLink);
              td.appendChild(div);
              tr.appendChild(td);
              tableBody.appendChild(tr);
          });

          if (to > hosts.length) {
              for (let i=hosts.length; i<to; i++) {
                  var tr = document.createElement("tr");
                  var td = document.createElement("td");
                  var div = document.createElement("div");
                  div.textContent = i;
                  td.appendChild(div);
                  tr.appendChild(td);
                  div = document.createElement("div");
                  td = document.createElement("td");
                  td.className='hide-on-mobile';
                  div.textContent = "";
                  td.appendChild(div);
                  tr.appendChild(td);
                  div = document.createElement("div");
                  td = document.createElement("td");
                  td.className='hide-on-mobile';
                  div.textContent = "";
                  td.appendChild(div);
                  tr.appendChild(td);
                  div = document.createElement("div");
                  td = document.createElement("td");
                  div.textContent = "";
                  td.appendChild(div);
                  tr.appendChild(td);
                  tableBody.appendChild(tr);
              }
          }

      }
</script>
    <main>
      <div class="container py-4">
        <header class="pb-3 mb-4 border-bottom">
          <img width="64" height="64" src="cartoon-spider.png" />
          <span class="fs-4">spiderman.py</span>
        </header>

        <div class="p-5 mb-4 bg-light rounded-3">
          <div class="container-fluid py-5">
            <div class="row">
              <div class="col-md-8">
                <p class="fs-4"><a href="https://github.com/mpfeifer/spiderman.py">Spiderman</a> is a <a href="https://www.python.org/">Pyhton</a> script that is connecting to port 80 on random hosts infinitly. If the connection attempt succeeds, it assumes that there is a webserver listening on that hosts port 80. Those hosts ip addresses are written down in an sqlite3 database file. This file's content is presented in this UI using the <a href="http://www.getbootstrap.com">Bootstrap 5.2 CSS-Framework</a> and <a href="http://www.php.net">PHP 8.4</a>.</p>
              </div>
              <div class="col-md-4 d-flex">
                <img src="spiderman.png" class="img-fluid rounded-3" />
              </div>
            </div>
          </div>
        </div>

        <div class="row align-items-md-stretch">
          <div class="col-md-12">
            <div class="h-100 p-5 bg-light border rounded-3">
              <h2>Recently discovered servers</h2>

              <table class="table table-striped table-md">
                <thead>
                  <tr>
                    <th scope="col">ID</th>
                    <th scope="col" class="hide-on-mobile">Timestamp</th>
                    <th scope="col" class="hide-on-mobile">Hostname</th>
                    <th scope="col">IPv4</th>
                  </tr>
                </thead>
                <tbody id="hosts-table">
                  <!-- filled by fillTable function -->
                </tbody>
              </table>

              <button class="btn btn-outline-secondary" onclick="gotoPage(prevPage)" type="button">Prev Page</button>
              <button class="btn btn-outline-secondary" onclick="gotoPage(nextPage)" type="button">Next Page</button>
              
            </div>
          </div>

          <footer class="pt-3 mt-4 text-muted border-top">
            &copy; Matthias 2025
          </footer>
        </div>
    </main>

    <article>
    </article>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>

    <script>
      fillTable(currentPage);
    </script>
  </body>

</html>
