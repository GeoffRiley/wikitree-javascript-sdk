<!DOCTYPE html>
<?php
$script = $_SERVER['PHP_SELF'];
?>
<html lang="en" dir="ltr" xml:lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
  <meta name="robots" content="noindex, nofollow"/>
  <title>WikiTree - Family Tree and Free Genealogy - wikitree-javascript-sdk - Example </title>
  <style type="text/css">

    /* By default, show the need-login section and not the logged-in section */
    /* Note this is *not* security, it's convenience. Logged-in-ness is double-checked inside API functions. */
    /* The getPerson() function will fail if you try to retrieve a person that the viewing user is not allowed to view. */

    BODY {
      margin: 0;
      padding: 0;
      font-family: verdana, arial, sans-serif;
      font-size: 13px;
      background-color: #fff;
    }

    P, DIV, TABLE {
      font-family: verdana, arial, sans-serif;
      font-size: 13px;
    }

    #HEADLINE {
      clear: both;
      margin: 0;
      padding: 10px 5% 10px 12%;
      text-align: left;
    }

    H1 {
      font-family: calibri, verdana, arial, sans-serif;
      font-weight: bold;
      color: #253b2f;
      background-color: #ffffff;
      font-size: 32pt;
      margin: 0;
    }

    #CONTENT {
      clear: both;
      margin: 0 5% 0 5%;
      padding: 10px 5% 10px 5%;
      background-color: #fff;
    }

    .MISC-PAGE {
      border: 4px solid #b7c0cf;
      overflow: auto;
    }

    #logged_in {
      display: none;
    }

    #need_login {
    }

    /* Make a box for our output, and put it next to a box where we'll dump some raw data for this demo. */
    #output {
      margin-top: 20px;
      border: 1px solid black;
      background-color: #f0f0f0;
      padding: 5px;
      min-height: 200px;
      width: 300px;
      float: left;
    }

    #raw {
      margin-top: 20px;
      margin-left: 20px;
      border: 1px solid black;
      background-color: #f0f0f0;
      padding: 5px;
      width: 400px;
      min-height: 200px;
      max-height: 500px;
      overflow: scroll;
      float: left;
    }

    /* Make our names look like links, though we don't go anywhere else. */
    .pseudo_link {
      text-decoration: underline;
      color: blue;
      cursor: pointer;
    }
  </style>

  <!-- For convenience, use JQuery for our Ajax interactions with the API. Use the cookie module to grab any existing user id. -->
  <script src="//ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
  <script src="//cdn.jsdelivr.net/npm/js-cookie@2/src/js.cookie.min.js"></script>
  <script src="wikitree.js"></script>

  <script type="text/javascript">

      // In the ready() function we run some code when the DOM is ready to go.
      $(document).ready(function () {

          wikitree.init({});
          wikitree.session.checkLogin({})
              .then(function (data) {
                  if (wikitree.session.loggedIn) {
                      /* We're already logged in and have a valid session. */
                      $('#need_login').hide();
                      $('#logged_in').show();
                  } else {
                      /* We're not yet logged in, but maybe we've been returned-to with an auth-code */
                      const x = window.location.href.split('?');
                      const queryParams = new URLSearchParams(x[1]);
                      if (queryParams.has('authcode')) {
                          const authcode = queryParams.get('authcode');
                          wikitree.session.clientLogin({'authcode': authcode})
                              .then(function (data) {
                                  if (wikitree.session.loggedIn) {
                                      /* If the auth-code was good, redirect back to ourselves without the authcode in the URL (don't want it bookmarked, etc). */
                                      window.location = 'index.html';
                                  } else {
                                      $('#need_login').show();
                                      $('#logged_in').hide();
                                  }
                              });
                      } else {
                          $('#need_login').show();
                          $('#logged_in').hide();
                      }
                  }
              });
          $('#login-name').text(wikitree.session.user_name);

      });

      // Given a user id, "walk" that user by retrieving their data and sticking it into the page.
      function walk(user_id) {

          // If we don't have a user_id, use the one from the cookie (the one for the logged-in user).
          if (!user_id) {
              user_id = getCookieDef('wikitree_wtb_UserID', '');
          }

          // Go get the person data.
          const p = new Person({user_id: user_id});
          p.load({fields: 'Id,Name,FirstName,MiddleName,LastNameAtBirth,LastNameCurrent,BirthDate,DeathDate,Father,Mother,Parents,Children'}).then(function (data) {
              // Raw JSON results dumped to display div
              $('#raw').html("<h2>Raw Results</h2>\n<pre>" + JSON.stringify(data, null, 4) + "</pre>");

              //<h2 id="walk_name"></h2>
              $('#walk_name').html(p.FirstName + ' ' + p.LastNameCurrent);

              //Father: <span id="walk_father"></span><br>
              if (p.Parents[p.Father]) {
                  const f = p.Parents[p.Father];
                  $('#walk_father').html("<span class='pseudo_link' onClick='walk(" + f.Id + ");'>" + f.FirstName + ' ' + f.LastNameCurrent + "</span>");
              } else {
                  $('#walk_father').html('');
              }

              //Mother: <span id="walk_mother"></span><br>
              if (p.Parents[p.Mother]) {
                  const m = p.Parents[p.Mother];
                  $('#walk_mother').html("<span class='pseudo_link' onClick='walk(" + m.Id + ");'>" + m.FirstName + ' ' + m.LastNameCurrent + "</span>");
              } else {
                  $('#walk_mother').html('');
              }

              //Children: <span id="walk_children"></span><br>
              if (p.Children) {
                  let html = '';
                  for (let cid in p.Children) {
                      const c = p.Children[cid];
                      if (html !== '') {
                          html += ' / ';
                      }
                      html += "<span class='pseudo_link' onClick='walk(" + c.Id + ");'>" + c.FirstName + ' ' + c.LastNameCurrent + "</span>";
                  }
                  $('#walk_children').html(html);
              } else {
                  $('#walk_mother').html('');
              }
          });
      }

      // Log the user out of apps.wikitree.com by deleting all the cookies
      function appsLogout() {
          wikitree.session.logout();
          document.location.href = 'http://apps.wikitree.com/<?php echo $script; ?>';
      }
  </script>

</head>
<body class="mediawiki ns-0 ltr page-Main_Page">
<?php include "/home/apps/www/header.htm"; ?>

<div id="HEADLINE">
  <h1><?php echo $script; ?></h1>
</div>

<div id="CONTENT" class="MISC-PAGE">

  <!-- This div is shown if the user is logged in. -->
  <div id="logged_in">
    <p>
      You are logged in to WikiTree as <span id="login-name">N</span>.
      You can <span class="pseudo_link" onClick="appsLogout();">logout</span>.
      You can "walk" through your family tree by clicking on the names below. The right-side will show the raw JSON
      output of the Person.load and related calls used at each step.
      Or you can return to <a href="http://apps.wikitree.com/apps/">Apps</a>.
    </p>

    <!-- This div has the spans filled in by our walk() function. -->
    <div id="output">
      <h2 id="walk_name"></h2>
      Father: <span id="walk_father"></span><br/>
      Mother: <span id="walk_mother"></span><br/>
      Children: <span id="walk_children"></span><br/>
      <br/>
      <span class="pseudo_link" onClick="walk()">Restart with your profile</span>
    </div>

    <!-- This div will hold the raw JSON output from a walk() call. -->
    <div id="raw"></div>

    <div style="clear:both;"></div>
  </div>

  <!-- This div is shown if the user is not logged in. -->
  <div id="need_login">
    <p>
      You are not currently logged in to apps.wikitree.com. In order to access your WikiTree ancestry, please sign in
      with your WikiTree.com credentials.
    </p>
    <form action="https://apps.wikitree.com/api.php" method="POST">
      <input type="hidden" name="action" value="clientLogin">
      <input type="hidden" name="returnURL"
             value="https://apps.wikitree.com/<?php echo $script; ?>">
      <input type="submit" class="button" value="Client Login">
    </form>

  </div>

</div>

<?php include "/home/apps/www/footer.htm"; ?>
</body>
</html>
