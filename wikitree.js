/*
 ======================================================================

 OBJECT: Wikitree

 ======================================================================
*/
const Wikitree = (function () {
	'use strict';
	var instance;

	function createInstance() {
		return {};
	}

	return {
		getInstance: function () {
			if (!instance) {
				instance = createInstance();
			}
			return instance;
		}
	}
})();

wikitree = Wikitree.getInstance();
wikitree.API_URL = '/api.php';

/*
 ======================================================================

 SUPPORT functions

 ======================================================================
 */
const getCookieDef = (name, def) => {
	var ret = Cookies.get(name);

	if (typeof ret === "undefined")
		ret = def;

	return ret;
};

/*
 ======================================================================

 OBJECT: Session

 ======================================================================
*/
const Session = function (opts) {
	'use strict';
	var _user_id;
	var _user_name;
	var _loggedIn;
	var _error;

	(function (opts) {
		_user_id = parseInt((opts && opts.user_id) ? opts.user_id : getCookieDef('wikitree_wtb_UserID', 0), 10);
		_user_name = (opts && opts.user_name) ? opts.user_name : getCookieDef('wikitree_wtb_UserName', '');
		_loggedIn = false;
		_error = '';
	})(opts);

	// Method for Session objects to check the current login.
	// Return a promise object (from our .ajax() call) so we can do things when this resolves.
	var checkLogin = function (opts) {
		if (opts && opts.user_id) {
			_user_id = opts.user_id;
		}
		if (opts && opts.user_name) {
			_user_name = opts.user_name;
		}

		var request = $.ajax({
			url: wikitree.API_URL,
			crossDomain: true,
			xhrFields: {withCredentials: true},
			type: 'POST',
			dataType: 'json',
			data: {'action': 'login', 'user_id': _user_id},
			// Local success handling to set our cookies.
			success: (data) => {
				if (data.login.result === _user_id) {
					Cookies.set('wikitree_wtb_UserID', _user_id, {'path': '/'});
					Cookies.set('wikitree_wtb_UserName', _user_name, {'path': '/'});
					_loggedIn = true;
				} else {
					Cookies.remove('wikitree_wtb_UserID', {'path': '/'});
					Cookies.remove('wikitree_wtb_UserName', {'path': '/'});
					_loggedIn = false;
				}
			},
			error: (xhr, status) => {
				Cookies.remove('wikitree_wtb_UserID', {'path': '/'});
				Cookies.remove('wikitree_wtb_UserName', {'path': '/'});
				_loggedIn = false;
				_error = status;
			}
		});

		return request.promise();
	};

	// Do an actual login through the server API with an Ajax call.
	// This is for bots where the script has a built-in/known username/password.
	var login = function (opts) {
		var email = (opts && opts.email) ? opts.email : '';
		var password = (opts && opts.password) ? opts.password : '';
		var request = $.ajax({
			url: wikitree.API_URL,
			crossDomain: true,
			xhrFields: {withCredentials: true},
			type: 'POST',
			dataType: 'json',
			data: {'action': 'login', 'email': email, 'password': password},

			// On successful POST return, check our data. Note from that data whether the login itself was
			// successful (setting session cookies if so). Call the user callback function when done.
			success: (data) => {
				if (data.login.result === 'Success') {
					_user_id = data.login.userid;
					_user_name = data.login.username;
					_loggedIn = true;
					Cookies.set('wikitree_wtb_UserID', _user_id);
					Cookies.set('wikitree_wtb_UserName', _user_name);
				} else {
					_loggedIn = false;
					Cookies.set('wikitree_wtb_UserID', _user_id);
					Cookies.set('wikitree_wtb_UserName', _user_name);
				}
			},

			// On failed POST/server error, act like a failed login.
			error: (xhr, status) => {
				_user_id = 0;
				_user_name = '';
				_loggedIn = false;
				_error = status;
				Cookies.set('wikitree_wtb_UserID', _user_id);
				Cookies.set('wikitree_wtb_UserName', _user_name);
			}
		});

		return request.promise();
	};

	// Do an actual login through the server API with an Ajax call.
	// This is for interactive apps where we sent the user to wikitree.com to login and got
	// back an auth code to use for login here.
	var clientLogin = function (opts) {
		var authcode = (opts && opts.authcode) ? opts.authcode : '';
		var request = $.ajax({
			url: wikitree.API_URL,
			crossDomain: true,
			xhrFields: {withCredentials: true},
			type: 'POST',
			dataType: 'json',
			data: {'action': 'clientLogin', 'authcode': authcode},

			// On successful POST return, check our data. Note from that data whether the login itself was
			// successful (setting session cookies if so).
			success: (data) => {
				//console.log(data);
				if (data.clientLogin.result === 'Success') {
					_user_id = data.clientLogin.userid;
					_user_name = data.clientLogin.username;
					_loggedIn = true;
					Cookies.set('wikitree_wtb_UserID', _user_id, {'path': '/'});
					Cookies.set('wikitree_wtb_UserName', _user_name, {'path': '/'});
				} else {
					_loggedIn = false;
					Cookies.set('wikitree_wtb_UserID', '', {'path': '/'});
					Cookies.set('wikitree_wtb_UserName', '', {'path': '/'});
				}
			},

			// On failed POST/server error, act like a failed login.
			error: (xhr, status) => {
				_user_id = 0;
				_user_name = '';
				_loggedIn = false;
				_error = status;
				Cookies.remove('wikitree_wtb_UserID', {'path': '/'});
				Cookies.remove('wikitree_wtb_UserName', {'path': '/'});
			}
		});

		return request.promise();
	};

	var logout = function () {
		_loggedIn = false;
		_user_id = 0;
		_user_name = '';
		_error = '';
		Cookies.remove('wikitree_wtb_UserID', {'path': '/'});
		Cookies.remove('wikitree_wtb_UserName', {'path': '/'});
	};

	return {
		checkLogin: checkLogin,
		login: login,
		clientLogin: clientLogin,
		logout: logout,
		get user_id() {
			return _user_id;
		},
		get user_name() {
			return _user_name;
		},
		get loggedIn() {
			return _loggedIn;
		},
		get error() {
			return _error;
		}
	}
};

wikitree.init = function (opts) {
	wikitree.session = new Session(opts);
};


/*
 ======================================================================

 OBJECT: Person

 ======================================================================
*/

const Person = function (opts) {
	'use strict';
	var _people;
	var _user_id;
	var _loaded;
	var _loading;
	var _status;

	(function (opts) {
		_user_id = (opts && opts.user_id) ? opts.user_id : 0;
		_loaded = false;
		_loading = false;
		_status = '';
		_people = {};
	})(opts);

	const _doLoad = function (opts) {
		// If we have a fields passed in, use those. If not, use a default set.
		var fields = 'Id,Name,FirstName,MiddleName,LastNameAtBirth,LastNameCurrent,BirthDate,DeathDate,Father,Mother';
		if (opts && opts.fields) {
			fields = opts.fields;
		}
		// Post our content to the server API, passing along the requested fields.
		// Use crossDomain=true in case we end up hosting this on something like apps.wikitree.com but configured
		// to query the live database/API at www.wikitree.com.
		var request = $.ajax(
			wikitree.API_URL,
			{
				crossDomain: true,
				xhrFields: {withCredentials: true},
				type: "POST",
				dataType: 'json',
				data: {'action': 'getPerson', 'key': _user_id, 'fields': fields, 'format': 'json'},

				// On success, we note that we're done loading. If we got data back, we store it in self and set loaded=true.
				success: (data) => {
					_loading = false;
					_status = data[0].status;
					if (!_status) {
						for (var x in data[0].person) {
							_people[x] = data[0].person[x];
						}
					} else {
						_user_id = 0;
					}
					_loaded = true;
				},

				// On error, report the "status" we got back.
				error: (xhr, status) => {
					_loading = false;
					_loaded = false;
					_status = 'Error in API query ' + status;
				}
			});

		return request.promise();
	};

	const load = function (opts) {
		// If this Person is already loaded, we're all done. If not, we (may) have work to do.
		if (!_loaded) {
			// Javascript will run right through the _doLoad() call below and it's possible this load() function will
			// get called again before loaded = true and before the first .ajax() call has returned.
			// We don't want to post to the server more than once.
			// If we're loading already, we don't have anything new to do.
			if (!_loading) {
				// Start loading our content from the server API.
				_loading = true;
				return _doLoad(opts);
			}
		}
	};

	const person = function (x) {
		return (x in _people) ? _people[x] : '';
	};

	return {
		load: load,
		person: person,
		get Id() {return person('Id')},
		get Name() {return person('Name')},
		get Gender() {return person('Gender')},
		get FirstName() {return person('FirstName')},
		get MiddleName() {return person('MiddleName')},
		get LastNameAtBirth() {return person('LastNameAtBirth')},
		get LastNameCurrent() {return person('LastNameCurrent')},
		get BirthDate() {return person('BirthDate')},
		get DeathDate() {return person('DeathDate')},
		get Father() {return person('Father')},
		get Mother() {return person('Mother')},
		get Parents() {return person('Parents')},
		get Children() {return person('Children')},
		get Siblings() {return person('Siblings')},
		get Spouses() {return person('Spouses')}
	}
};
