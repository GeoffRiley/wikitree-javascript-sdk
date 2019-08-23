<!DOCTYPE html>
<?php
$script = $_SERVER['PHP_SELF'];
?>
<html lang="en" dir="ltr" xml:lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
  <meta name="robots" content="noindex, nofollow"/>
  <title>WikiTree - Family Tree and Free Genealogy - Apps - Demo</title>
  <link rel="stylesheet" href="https://www.wikitree.com/css/main-new.css?2" type="text/css"/>

  <style type="text/css">

    /* By default, show the need-login section and not the logged-in section */
    /* Note this is *not* security, it's convenience. Logged-in-ness is double-checked inside API functions. */
    /* The getPerson() function will fail if you try to retrieve a person that the viewing user is not allowed to view. */
    #logged_in {
      display: none;
    }

    #need_login {
    }

    #output {
      margin-top: 20px;
      border: 1px solid black;
      background-color: #f0f0f0;
      padding: 5px;
      min-height: 200px;
      width: 700px;
    }

  </style>

  <!-- For convenience, use JQuery for our Ajax interactions with the API. Use the cookie module to grab any existing user id. -->
  <script src="//ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
  <script src="//cdn.jsdelivr.net/npm/js-cookie@2/src/js.cookie.min.js"></script>
  <script src="wikitree.js"></script>

  <script type="text/javascript">
      // Define the URL where our API requests will be POSTed.
      const API_URL = 'https://apps.wikitree.com/api.php';
      const THIS_SCRIPT = 'https://apps.wikitree.com/<?php echo $script; ?>';

      // When the document is ready, see if we have a user session with checkLogin().
      // If no, then show the "need login" div with the form and button to post to the API clientLogin() function.
      // If yes, then hide that and show our function content.
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
                                      window.location = 'api_demo.php';
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
                  $('#user_name_label').text(wikitree.session.user_name);
                  $('#user_id_label').text(wikitree.session.user_id);
              });
      });


      // This function retrieves person information. The "key" that is sent to the API "getPerson" action can be a numeric user_id or the
      // WikiTreeID (e.g. Adams-35). The output here is just dumped into a div for display.
      function getPerson(user_id_or_name, fields) {
          // Add to our putput what we're looking for.
          $('#output').html('The user id/name provided is:' + user_id_or_name);

          // Go get the person data.
          $.ajax({
              url: API_URL,
              crossDomain: true,
              xhrFields: {withCredentials: true},
              type: "POST",
              dataType: 'json',
              data: {'action': 'getPerson', 'key': user_id_or_name, 'fields': fields, 'format': 'json'},

              // The returned data is in JSON format. Turn that into a formatted string and display it.
              success: (data) => {
                  $('#output').append(`<br/><br/>The full results are:<br/><pre>${JSON.stringify(data, null, 4)}</pre>`);
              },

              // On error, report the "status" we got back.
              error: (xhr, status) => {
                  $('#output').append(`<br/>There was an error calling getPerson:${status}.`);
              }
          });

      }

      // Get the Privacy level description
      function getPrivacyLevels() {
          $.ajax({
              url: API_URL,
              type: "POST",
              crossDomain: true,
              xhrFields: {withCredentials: true},
              dataType: 'json',
              data: {'action': 'getPrivacyLevels'},
              success: data => {
                  $('#output').html(`The full results are:<br/><pre>${JSON.stringify(data, null, 4)}</pre>`);
              },
              error: (xhr, status) => {
                  $('#output').append(`<br/>There was an error getting the person:${status}`);
              }
          });
      }

      // Log the user out of apps.wikitree.com by deleting all the cookies
      function appsLogout() {
          Cookies.remove('wikitree_wtb_UserID', {'path': '/'});
          Cookies.remove('wikitree_wtb_UserName', {'path': '/'});
          document.location.href = THIS_SCRIPT;
      }

      // This function retrieves a Watchlist.
      function getWatchlist() {
          const fields = $('#gwfields').val();
          // Go get the watchlist data.
          $.ajax({
              url: API_URL,
              type: "POST",
              crossDomain: true,
              xhrFields: {withCredentials: true},
              dataType: 'json',
              data: {'action': 'getWatchlist', 'fields': fields, 'format': 'json'},
              success: data => {
                  $('#output').append(`<br/><br/>The full results are:<br/><pre>${JSON.stringify(data, null, 4)}</pre>`);
              },
              error: (xhr, status) => {
                  $('#output').append(`<br/>There was an error getting the person:${status}`);
              }
          });
      }

      // This function retrieves person/space information.
      function getProfile(key, fields) {
          // Add to our putput what we're looking for.
          $('#output').html(`The key provided is:${key}`);

          // Go get the person data.
          $.ajax({
              url: API_URL,
              type: "POST",
              crossDomain: true,
              xhrFields: {withCredentials: true},
              dataType: 'json',
              data: {'action': 'getProfile', 'key': key, 'fields': fields, 'format': 'json'},
              success: data => {
                  $('#output').append(`<br/><br/>The full results are:<br/><pre>${JSON.stringify(data, null, 4)}</pre>`);
              },
              error: (xhr, status) => {
                  $('#output').append(`<br/>There was an error getting the profile:${status}`);
              }
          });
      }

      // This function retrieves person bio
      function getBio(key) {
          // Add to our putput what we're looking for.
          $('#output').html(`The key provided is:${key}`);

          // Go get the person data.
          $.ajax({
              url: API_URL,
              type: "POST",
              crossDomain: true,
              xhrFields: {withCredentials: true},
              dataType: 'json',
              data: {'action': 'getBio', 'key': key, 'format': 'json'},
              success: data => {
                  $('#output').append(`<br/><br/>The full results are:<br/><pre>${JSON.stringify(data, null, 4)}</pre>`);
              },
              error: (xhr, status) => {
                  $('#output').append(`<br/>There was an error getting the profile:${status}`);
              }
          });
      }

      // This function retrieves ancestors for a profile
      function getAncestors(key, depth) {
          // Add to our putput what we're looking for.
          $('#output').html(`The key provided is:${key}<br/>The depth provided is:${depth}<br/>`);

          // Go get the person data.
          $.ajax({
              url: API_URL,
              type: "POST",
              crossDomain: true,
              xhrFields: {withCredentials: true},
              dataType: 'json',
              data: {'action': 'getAncestors', 'key': key, 'format': 'json'},
              success: data => {
                  $('#output').append(`<br/><br/>The full results are:<br/><pre>${JSON.stringify(data, null, 4)}</pre>`);
              },
              error: (xhr, status) => {
                  $('#output').append(`<br/>There was an error getting the profile:${status}`);
              }
          });
      }

      // Retrieve relatives of one or more id.
      function getRelatives(keys, getParents, getSpouses, getChildren, getSiblings) {

          // Add to our putput what we're looking for.
          $('#output').html(`The keys provided are:${keys}<br/>Get Parents:${getParents}<br/>Get Spouses:${getSpouses}<br/>Get Children:${getChildren}<br/>Get Siblings:${getSiblings}<br/>`);

          $.ajax({
              url: API_URL,
              type: "POST",
              crossDomain: true,
              xhrFields: {withCredentials: true},
              dataType: 'json',
              data: {
                  'action': 'getRelatives',
                  'keys': keys,
                  'getParents': getParents,
                  'getSpouses': getSpouses,
                  'getChildren': getChildren,
                  'getSiblings': getSiblings,
                  'format': 'json'
              },
              success: data => {
                  $('#output').append(`<br/><br/>The full results are:<br/><pre>${JSON.stringify(data, null, 4)}</pre>`);
              },
              error: (xhr, status) => {
                  $('#output').append(`<br/>There was an error getting the person:${status}`);
              }
          });
      }

      // Retrieve DNA TEsts for a profile
      function getDNATestsByTestTaker() {

				const key = $('#key').val();
				$('#output').html(`<br/>Getting DNA Tests for profile: ${key}<br/><br/>`);
          $.ajax({
              url: API_URL,
              type: "POST",
              crossDomain: true,
              xhrFields: {withCredentials: true},
              dataType: 'json',
              data: {'action': 'getDNATestsByTestTaker', 'key': key},
              success: data => {
                  $('#output').html(`<br/><br/>The full results are:<br/><pre>${JSON.stringify(data, null, 4)}</pre>`);
              },
              error: (xhr, status) => {
                  $('#output').append(`<br/>There was an error getting the person:${status}`);
              }
          });
      }

      // Retrieve DNA Test Connections for a profile + dna id.
      function getConnectedProfilesByDNATest() {

				const key = $('#key2').val();
				const dna_id = $('#dna_id2').val();
				$('#output').html(`<br/>Getting profiles connected by DNA Test to: key:${key}, DNA ID:${dna_id}<br/><br/>`);
          $.ajax({
              url: API_URL,
              type: "POST",
              crossDomain: true,
              xhrFields: {withCredentials: true},
              dataType: 'json',
              data: {'action': 'getConnectedProfilesByDNATest', 'key': key, 'dna_id': dna_id},
              success: data => {
                  $('#output').html(`<br/><br/>The full results are:<br/><pre>${JSON.stringify(data, null, 4)}</pre>`);
              },
              error: (xhr, status) => {
                  $('#output').append(`<br/>There was an error getting the person:${status}`);
              }
          });
      }

      // Retrieve Connected profiles by DNA Test object and test id.
      function getConnectedDNATestsByProfile() {

				const key = $('#key3').val();
				$('#output').html(`<br/>Getting DNA Tests and the test-taker profiles for profiles connected by DNA Test to: key:${key}`);
          $.ajax({
              url: API_URL,
              type: "POST",
              crossDomain: true,
              xhrFields: {withCredentials: true},
              dataType: 'json',
              data: {'action': 'getConnectedDNATestsByProfile', 'key': key},
              success: data => {
                  $('#output').html(`<br/><br/>The full results are:<br/><pre>${JSON.stringify(data, null, 4)}</pre>`);
              },
              error: (xhr, status) => {
                  $('#output').append(`<br/>There was an error getting the person:${status}`);
              }
          });
      }


  </script>

</head>

<body class="mediawiki ns-0 ltr page-Main_Page">
<?php include "/home/apps/www/header.htm"; ?>

<div id="HEADLINE">
  <h1><?php echo $script ?></h1>
</div>

<div id="CONTENT" class="MISC-PAGE">

  <!-- This div is shown if the user is logged in. -->
  <div id="logged_in">
    <p>
      Welcome to the beta version of apps.wikitree.com, where WikiTree community members can develop applications for
      working with WikiTree using a simple API.
    </p>

    <p>
      You appear to be logged in as WikiTree user <span class="user_name_label">?</span> (#<span
      class="user_id_label">?</span>). If this
      is incorrect, please <span style="cursor:pointer;color:blue;text-decoration:underline;" onClick="appsLogout();">logout</span>
      and then sign back in.
      Or you can return to <a href="https://apps.wikitree.com/apps/">Apps</a>.
    </p>

    <b>Action: getPerson()</b>
    <blockquote>
      <form action="#" onSubmit='return false'>
        User ID or Name: <input type=text id="user_id_or_name" name="user_id_or_name" value=""> (e.g. "Adams-35" or
        "978")<br/>
        Fields: <input type=text id="fields" name="fields" value="Id,Name,FirstName,LastNameCurrent,Mother,Father"
                       size=120><br/>
        Valid field values:
        <blockquote>Id, Name, FirstName, MiddleName, LastNameAtBirth, LastNameCurrent, Nicknames, LastNameOther,
          RealName, Prefix, Suffix, BirthLocation, DeathLocation, Gender, BirthDate,DeathDate, Photo, Father,Mother,
          Privacy, Parents,Siblings,Spouses,Children, Manager
        </blockquote>

        <input type=button value="Get Person" onClick="getPerson($('#user_id_or_name').val(), $('#fields').val())">
      </form>
    </blockquote>

    <b>Action: getProfile()</b>
    <blockquote>
      <form action="#" onSubmit='return false'>
        WikiTree ID, Space:Name or Page ID: <input type=text id="getProfileKey" name="key" value=""> (e.g. "Adams-35" or
        "Space:Allied_POW_camps" or "7933538")<br/>
        Fields: <input type=text id="getProfileFields" name="fields" value="" size=120><br/>
        <input type=button value="Get Profile"
               onClick="getProfile($('#getProfileKey').val(),$('#getProfileFields').val())">
      </form>
    </blockquote>

    <b>Action: getBio()</b>
    <blockquote>
      <form action="#" onSubmit='return false'>
        WikiTree ID or User ID: <input type=text id="biokey" name="biokey" value=""> (e.g. "Adams-35" "3636")<br/>
        <input type=button value="Get Bio" onClick="getBio($('#biokey').val())">
      </form>
    </blockquote>

    <b>Action: getWatchlist()</b>
    <blockquote>
      <form action="#" onSubmit='return false'>
        <input type=button value="Get Watchlist" onClick="getWatchlist();"><br/>
        Fields: <input type=text id="gwfields" name="gwfields" value="Id,Name,FirstName,LastNameCurrent,Mother,Father"
                       size=120><br/>
      </form>
    </blockquote>

    <b>Action: getAncestors()</b>
    <blockquote>
      <form action="#" onSubmit='return false'>
        User ID or Name: <input type=text id="ancestor_key" name="key" value=""> (e.g. "Adams-35" "3636")<br/>
        Depth (1-10): <input type=text id="ancestor_depth" name="depth" value=""> (default: 5)<br/>
        <input type=button value="Get Ancestors"
               onClick="getAncestors($('#ancestor_key').val(), $('#ancestor_depth').val())">
      </form>
    </blockquote>


    <b>Action: getRelatives()</b>
    <blockquote>
      <form action="#" onSubmit='return false'>
        User IDs or Names: <input type=text id="relatives_keys" name="relatives_keys" value=""> (e.g.
        "Adams-35,Tesla-1")<br/>
        <input type=checkbox name="relatives_parents" id="relatives_parents" value='1'> Get Parents<br/>
        <input type=checkbox name="relatives_spouses" id="relatives_spouses" value='1'> Get Spouses<br/>
        <input type=checkbox name="relatives_children" id="relatives_children" value='1'> Get Children<br/>
        <input type=checkbox name="relatives_siblings" id="relatives_siblings" value='1'> Get Siblings<br/>
        <input type=button value="Get Relatives"
               onClick="getRelatives($('#relatives_keys').val(), $('#relatives_parents').val(), $('#relatives_spouses').val(), $('#relatives_children').val(), $('#relatives_siblings').val())">
      </form>
    </blockquote>

    <b>Action: getPrivacyLevels()</b>
    <blockquote>
      <form action="#" onSubmit='return false'>
        <input type=button value="Get Privacy Levels" onClick="getPrivacyLevels();">
      </form>
    </blockquote>


    <b>Function: getDNATestsByTestTaker()</b>
    <blockquote>
      <form action="#" onSubmit='return false'>
        User ID or Names: <input type=text id="key" name="key" value="" size=30> (e.g. "32" or "Whitten-1")<br/>
        <input type=button value="Get DNA Tests" onClick="getDNATestsByTestTaker()">
      </form>
    </blockquote>


    <b>Function: getConnectedProfilesByDNATest()</b>
    <blockquote>
      <form action="#" onSubmit='return false'>
        User ID or Name: <input type=text id="key2" name="key2" value="" size=30> (e.g. "32" or "Whitten-1")<br/>
        DNA Test ID: <input type=text id="dna_id2" name="dna_id2" value="" size=30><br/>
        <input type=button value="Get Connected Profiles" onClick="getConnectedProfilesByDNATest()">
      </form>
    </blockquote>


    <b>Function: getConnectedDNATestsByProfile()</b>
    <blockquote>
      <form action="#" onSubmit='return false'>
        User ID or Name: <input type=text id="key3" name="key3" value="" size=30> (e.g. "32" or "Whitten-1")<br/>
        <input type=button value="Get Conected DNA Tests" onClick="getConnectedDNATestsByProfile()">
      </form>
    </blockquote>


    <b>Output appears here</b><br/>
    <div id='output'></div>
  </div>

  <!-- This div is shown if the user is not logged in. -->
  <div id="need_login">
    <p>
      You are not currently logged in to apps.wikitree.com. In order to access
      your WikiTree ancestry, please sign in with your WikiTree.com credentials.
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
