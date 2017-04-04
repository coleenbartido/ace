//MAIN MODULE
angular.module('aceWeb', ['aceWeb.controller','aceWeb.directive','ui.router','ngStorage','ngMessages', 'ui.bootstrap', 'ngTouch', 'ngAnimate','checklist-model','luegg.directives','angular.filter','chart.js','ngCapsLock','ngAnimate']) //kailangan yang ui.router pag gagamit ng $stateProvider. Yung 'aceWeb' naman is yung name ng module. Nakalink sya dun sa body tag ng index.html file which tells na yung module na 'aceWeb' ay e e implement sa html file na yun. Lahat ng may "app." ay under ng 'aceWeb' module


// <------------------------------------------------------------------>


.config(['$stateProvider', '$urlRouterProvider', '$locationProvider', '$httpProvider', function($stateProvider, $urlRouterProvider, $locationProvider, $httpProvider)
{

 	$stateProvider

	  .state('login', // eto naman yung name ng state. So pag kailangan natin pumunta sa ibang view ito lang kailangan natin gamitin for ex: $state.go('login')
	  {
	    url: '/', // eto naman ung customizable url na pwede nating e set. So pag '' lang yung nilagay natin natin it means na sa domain lang ng website tayo pupunta para ma accesss yung view which is http://localhost/aceWeb/
	    templateUrl: 'templates/common/login.html', // eto yung location ng view sa project directory
	    controller: 'LoginController', // eto yung pangalan ng controller na ilalagay sa html file
      accessLevel: 0
	  })

    .state('forgotPassword',
    {
      url: '/forgotpassword',
      templateUrl: 'templates/common/forgot-password.html',
      controller: 'ForgotPasswordController',
      accessLevel: 0
    })

    .state('resetPassword',
    {
      url: '/resetpassword?email&hashcode',
      templateUrl: 'templates/common/reset-password-form.html',
      controller: 'ForgotPasswordController'
    })

    .state('accountSetup',
    {
      url: '/accountsetup?email&hashcode',
      templateUrl: 'templates/common/account-setup.html',
      controller: 'AccountSetupController'
    })

    .state('errorInvalidLink',
    {
      url: '/invalidlink',
      templateUrl: 'templates/common/error-invalid-link.html'
    })

    .state('404',
    {
      templateUrl: 'templates/common/404.html'
    })


    //FACULTY
    //<-------------------------------------------------------------------------->


	  .state('faculty',
    {
	    templateUrl: 'templates/faculty/faculty.html',
      loginRequired: true,
      accessLevel: 3,
	    controller: 'FacultyController'
	  })

    .state('faculty.referralForm',
    {
      url: '/referralform',
      templateUrl: 'templates/faculty/referral-form.html',
      loginRequired: true,
      accessLevel: 3,
      controller: 'ReferralFormController'
    })

    .state('faculty.messaging',
    {
      url: '/messaging/faculty',
      templateUrl: 'templates/faculty/messaging.html',
      loginRequired: true,
      accessLevel: 3,
      controller: 'MessagesController'
    })

    .state('faculty.settings',
    {
      url: '/settings/faculty',
      templateUrl: 'templates/faculty/settings.html',
      loginRequired: true,
      accessLevel: 3,
      controller: 'SettingsController'
    })

    .state('faculty.404Inside',
    {
      templateUrl: 'templates/common/404-inside.html'
    })


    //ADMIN
    //<-------------------------------------------------------------------------->


	  .state('admin',
    {
	    templateUrl: 'templates/admin/admin.html',
	    controller: 'AdminController',
      loginRequired: true,
      accessLevel: 2
	  })

    .state('admin.reports',
    {
      url: '/reports',
      templateUrl: 'templates/admin/reports.html',
      controller: 'ReportsController',
      loginRequired: true,
      accessLevel: 2
    })

    .state('admin.messaging',
    {
      url: '/messaging/admin',
      templateUrl: 'templates/admin/messaging.html',
      controller: 'MessagesController',
      loginRequired: true,
      accessLevel: 2

    })

    .state('admin.manageFaculty',
    {
      url: '/manage/faculty',
      templateUrl: 'templates/admin/manage-faculty.html',
      controller: 'ManageFacultyController',
      loginRequired: true,
      accessLevel: 2
    })

    .state('admin.manageStudents',
    {
      url: '/manage/students',
      templateUrl: 'templates/admin/manage-students.html',
      controller: 'ManageStudentController',
      loginRequired: true,
      accessLevel: 2
    })

    .state('admin.manageDatabase',
    {
      url: '/manage/database',
      templateUrl: 'templates/admin/manage-database.html',
      controller: 'DatabaseController',
      loginRequired: true,
      accessLevel: 2
    })

    .state('admin.summary',
    {
      url: '/summary',
      templateUrl: 'templates/admin/summary.html',
      controller: 'SummaryController',
      loginRequired: true,
      accessLevel: 2

    })

    .state('admin.settings',
    {
      url: '/settings/admin',
      templateUrl: 'templates/admin/settings.html',
      controller: 'SettingsController',
      loginRequired: true,
      accessLevel: 2

    })

    .state('admin.404Inside',
    {
      templateUrl: 'templates/common/404-inside.html'
    })


    /*.state('admin.newFaculty',
    {
      url: '/admin/new/faculty',
      templateUrl: 'templates/admin/new-faculty.html',
      controller: 'FacultyController',
      loginRequired: true,
      accessLevel: 2
    })

    .state('admin.deleteFaculty',
    {
      url: '/admin/delete/faculty',
      templateUrl: 'templates/admin/delete-faculty.html',
      controller: 'FacultyController',
      loginRequired: true,
      accessLevel: 2
    })*/



    //SUPERADMIN
    //<-------------------------------------------------------------------------->


	  .state('superadmin',
    {
	    templateUrl: 'templates/superadmin/superadmin.html',
	    controller: 'SuperAdminController',
      loginRequired: true,
      accessLevel: 1
	  })

    .state('superadmin.manageAdmin',
    {
      url: '/manage/admin',
      templateUrl: 'templates/superadmin/manage-admin.html',
      controller: 'ManageAdminController',
      loginRequired: true,
      accessLevel: 1
    })

    .state('superadmin.settings',
    {
      url: '/settings/superadmin',
      templateUrl: 'templates/superadmin/settings.html',
      controller: 'SettingsController',
      loginRequired: true,
      accessLevel: 1
    })

    .state('superadmin.404Inside',
    {
      templateUrl: 'templates/common/404-inside.html'
    })


    /*.state('superadmin.newAdmin',
    {
      url: '/superadmin/new/admin',
      templateUrl: 'templates/superadmin/new-admin.html',
      controller: 'AdminController',
      loginRequired: true,
      accessLevel: 1
    })

    .state('superadmin.deleteAdmin',
    {
      url: '/superadmin/delete/admin',
      templateUrl: 'templates/superadmin/delete-admin.html',
      controller: 'AdminController',
      loginRequired: true,
      accessLevel: 1

    })*/




    $urlRouterProvider.otherwise(function($injector, $location)
    {
      var state = $injector.get('$state');

      var authService = $injector.get('AuthService');

      if(localStorage.getItem("ngStorage-currentUser") === null)
      {
        state.go('404');
      }
      else
      {
        if(authService.getRole() == 3)
        {
          state.go('faculty.404Inside');
        }
        else if(authService.getRole() == 2)
        {
          state.go('admin.404Inside');
        }
        else if(authService.getRole() == 1)
        {
          state.go('superadmin.404Inside');
        }
      }
    }); //fallback page 404 error page

    $locationProvider.html5Mode(true); //remove # on url

    $httpProvider.interceptors.push('AuthInterceptor');

}])


// <-------------------------HTTP-INTERCEPTOR----------------------------------------->


.factory('AuthInterceptor', function ($rootScope, $q, $localStorage, AUTH_EVENTS, $timeout)
{
  return {

    request: function (config)
    {
      if($localStorage.currentUser)
      {
        config.headers.Authorization = 'Bearer ' + $localStorage.currentUser;
      }

      return config;
    },

    responseError: function (response)
    {
      if(response.status == 401 || response.status == 403)
      {
        $rootScope.$broadcast({
        401: AUTH_EVENTS.notAuthenticated,
        403: AUTH_EVENTS.notAuthorized
        }[response.status], response);
      }
      return $q.reject(response);
    }
  };
})


// <---------------------SERVICES--------------------------------------------->


.service('AuthService', function ($q, $http, $localStorage, config)
{
  var email = '';
  var role = '';
  var name = '';

  function getPayLoad()
  {
    try
    {
      var payload = JSON.parse(window.atob($localStorage.currentUser.split('.')[1]));
      return payload;
    }
    catch (e)
    {
      return false;
    }
  }

  function destroyUserCredentials()
  {
    email = '';
    role = '';
    name = '';
    $http.defaults.headers.common.Authorization = '';
    $localStorage.$reset();
  }

  this.getEmail = function ()
  {
    if(getPayLoad())
    {
      email = getPayLoad().data.email;
    }

    return email
  }

  this.getRole = function ()
  {
    if(getPayLoad())
    {
      role = getPayLoad().data.role;
    }

    return role
  }

  this.getName = function ()
  {
    if(getPayLoad())
    {
      name = getPayLoad().data.name;
    }

    return name
  }

  this.storeUserCredentials = function (token)
  {
    // store username and token in local storage to keep user logged in between page refreshes
    $localStorage.currentUser = token;

    // add jwt token to auth header for all requests made by the $http service
    $http.defaults.headers.common.Authorization = 'Bearer ' + token;
  }

  this.logout = function()
  {
    destroyUserCredentials();
  }

  this.authenticate = function()
  {
    return $http({
      method: 'POST',
      url: config.apiUrl + '/auth',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'}
    })
  }
})


// <------------------------------------------------------------------>


.run(function ($rootScope, $http, $location, $localStorage, $state, AuthService)
{
  $rootScope.$on('$stateChangeStart', function(event, toState, toParams, fromState, fromParams)
  {//&& AuthService.getRole() != toState.accessLevel
    if (toState.loginRequired && fromState.accessLevel != 0)
    {
      AuthService.authenticate();
    }

    //needs improvement to prevent empty value in localstorage
    if (toState.loginRequired && !$localStorage.currentUser)
    {
      event.preventDefault();
      AuthService.logout();
    }

    if(($localStorage.currentUser && toState.accessLevel == 0) || (toState.loginRequired && AuthService.getRole() != toState.accessLevel))
    {
      event.preventDefault();

      if(AuthService.getRole() == 3)
      {
        $state.go('faculty.referralForm');
      }
      else if(AuthService.getRole() == 2)
      {
        $state.go('admin.reports');
      }
      else if(AuthService.getRole() == 1)
      {
        $state.go('superadmin.manageAdmin');
      }
    }
  })
})
