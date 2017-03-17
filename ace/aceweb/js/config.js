//constants (environment variable)
angular.module('aceWeb')

.constant('config', {
	apiUrl: '../../../ace/webservice/public'
})

.constant('AUTH_EVENTS', {
	notAuthenticated: 'auth-not-authenticated',
	notAuthorized: 'auth-not-authorized'
})
