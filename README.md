wikitree-javascript-sdk
=======================
Javascript library to work with the WikiTree API functions.

* 21st August 2019: Updated to use the module pattern and current version of the Cookie Plugin.

## Prerequisites
* jQuery 1.10 or higher (may work with lower versions)
* jQuery Cookie Plugin 2.2.1 or later (https://github.com/js-cookie/js-cookie)

## Usage
````javascript
// Load scripts
<script src="//ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
<script src="//cdn.jsdelivr.net/npm/js-cookie@2/src/js.cookie.min.js"></script>
<script src="wikitree.js"></script>

<script type="text/javascript">

	wikitree.init({});
	wikitree.session.checkLogin().then(function(data){ ... });

	wikitree.session.login( { email: 'xxx', password: 'yyyy' }).then(function(data) {
	});

	const p = new Person( { user_id: nnnnn } );
	p.load({}).then(function(data){ 
	});

</script>
````
## Example

The documentation here is incomplete as the SDK (and the API itself) are all in early development.
The index.html file has a decent example of usage. 

A hosted version is at: http://apps.wikitree.com/apps/riley9287/wikitree-javascript-sdk/

## Objects
### Wikitree object
This is a singleton instance intended to contain the current session details.

#### Methods
* `getinstance()` *class* method to retrieve a pointer to the allocated instance.
* `create_instance()` **private** *class* method to create an initial instance: only called once, automatically, upon first use of the method `getinstance()`
* `init()` initialises the singleton instance of the Wikitree object.

#### Properties
* `instance` **private** property holding the only instance of the Wikitree object.
* `API_URL` location of the api protocol socket.
* `session` default session, see `Session` object.

### Session object
This object manages the logging in and authorisation for communications with the api.

#### Methods
* `checkLogin({user_id: nnn, user_name: 'AAA-nnn'})` 
  * performs a `login` using `user_id`.
    * On success: if the `user_id` is valid, sets the browser cookies and the logged in flag.
    * On fail: if the `user_id` is invalid, clears the browser cookies and the logged in flag.
    * On error: clears the browser cookies and the logged in flag.
* `login({email: 'email@address', password: 'password'})`
  * performs a `login` using `email` and `password`.
    * On success: saves `username` and `userid`, sets the browser cookies and the logged in flag.
    * On fail: **sets** the browser cookies but **clears** the logged in flag.
    * On error: clears the browser cookies, the logged in flag and the stored `username` and `userid`, also saves the status in `error`.
* `clientLogin({authcode: 'authcode'})`
  * performs a `clientLogin` using the `authcode` returned from an external login process.
    * On success: saves `username` and `userid`, sets the browser cookies and the logged in flag.
    * On fail: **sets** the browser cookies but **clears** the logged in flag.
    * On error: clears the browser cookies, the logged in flag and the stored `username` and `userid`, also saves the status in `error`.
* `logout()`
  * no communication with service, just clears all identification elements and clears the logged in flag.

#### Properties
* `user_id` **read only** return stored `user_id` (numeric).
* `user_name` **read only** return stored `user_name` (string).
* `loggedIn` **read only** returns logged in status (boolean).
* `error` **read only** return last stored error status (string).
* `_user_id` **private** internal storage.
* `_user_name` **private** internal storage.
* `_loggedIn` **private** internal storage.
* `_error` **private** internal storage.

### Person object
Controls the communication with the api for the loading of person information.

#### Methods
* `load({fields: 'list of fields to load'})`
  * performs a `getPerson` request using either the passed field list, or a default field list ('Id,Name,FirstName,MiddleName,LastNameAtBirth,LastNameCurrent,BirthDate,DeathDate,Father,Mother').
    * On success: stores any returned person records in the person array.
    * On error: sets the status to reflect any returned error.
* `person('fieldname')`
  * looks up a specific field in the person array and returns the appropriate value, if field does not exist then an empty string is returned.


#### Properties
* `Id` **read only** return the value of this field in the person array.
* `Name` **read only** return the value of this field in the person array.
* `Gender` **read only** return the value of this field in the person array.
* `FirstName` **read only** return the value of this field in the person array.
* `MiddleName` **read only** return the value of this field in the person array.
* `LastNameAtBirth` **read only** return the value of this field in the person array.
* `LastNameCurrent` **read only** return the value of this field in the person array.
* `BirthDate` **read only** return the value of this field in the person array.
* `DeathDate` **read only** return the value of this field in the person array.
* `Father` **read only** return the value of this field in the person array.
* `Mother` **read only** return the value of this field in the person array.
* `Parents` **read only** return the value of this field in the person array.
* `Children` **read only** return the value of this field in the person array.
* `Siblings` **read only** return the value of this field in the person array.
* `Spouses` **read only** return the value of this field in the person array.

If any fields are required that are not in the above list, they can be obtained by using the `person()` method.

