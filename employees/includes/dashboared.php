<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dynamic Page Loader</title>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

  <?php include 'header.php'; ?>

  <div id="content">
    <?php include 'pages/main.php'; ?>
  </div>

  <?php include 'includes/footer.php'; ?>

  <script>
    $(document).ready(function() {
      function loadPage(page) {
        $('#content').load('loader.php?page=' + page, function(response, status, xhr) {
          if (status == "error") {
            $('#content').html("An error occurred: " + xhr.status + " " + xhr.statusText);
          } else {
            console.log(page + " loaded successfully");
          }
        });
      }

      $('a[data-page]').click(function(e) {
        e.preventDefault();
        var page = $(this).data('page');
        loadPage(page);
      });

      // Default page
      loadPage('main');
    });
  </script>
</body>
</html>
