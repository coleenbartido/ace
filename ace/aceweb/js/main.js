//MAIN MODULE
angular.module('aceWeb', ['oc.lazyLoad','ui.router','ngStorage','ngMessages','ui.bootstrap','ngTouch','ngAnimate','angular.filter','ngCapsLock'])


// <------------------------------------------------------------------>


.config(['$stateProvider', '$urlRouterProvider', '$locationProvider', '$httpProvider', '$ocLazyLoadProvider', function($stateProvider, $urlRouterProvider, $locationProvider, $httpProvider, $ocLazyLoadProvider)
{
  $ocLazyLoadProvider.config({
    modules: [{
      name: 'chart.js',
      files: ["lib/chart/Chart.min.js","lib/chart/angular-chart.min.js","lib/chart/html2canvas.js"],
      serie: true
    },
    {
      name: 'checklist-model',
      files: ["lib/checklist-model.js"]
    },
    {
      name: 'luegg.directives',
      files: ["lib/scrollglue.js"]
    }]
  });

 	$stateProvider

	  .state('login', // eto naman yung name ng state. So pag kailangan natin pumunta sa ibang view ito lang kailangan natin gamitin for ex: $state.go('login')
	  {
	    url: '/', // eto naman ung customizable url na pwede nating e set. So pag '' lang yung nilagay natin natin it means na sa domain lang ng website tayo pupunta para ma accesss yung view which is http://localhost/aceWeb/
	    templateUrl: 'templates/common/login.html', // eto yung location ng view sa project directory
	    controller: 'LoginController', // eto yung pangalan ng controller na ilalagay sa html file
      accessLevel: 0,
      resolve: {
        loadFiles: ['$ocLazyLoad', function ($ocLazyLoad) {
          return $ocLazyLoad.load(['js/components/controller/common/LoginController.js']);
        }]
      }
	  })

    .state('forgotPassword',
    {
      url: '/forgotpassword',
      templateUrl: 'templates/common/forgot-password.html',
      controller: 'ForgotPasswordController',
      accessLevel: 0,
      resolve: {
        loadFiles: ['$ocLazyLoad', function ($ocLazyLoad) {
          return $ocLazyLoad.load(['js/components/controller/common/ForgotPasswordController.js']);
        }]
      }
    })

    .state('resetPassword',
    {
      url: '/resetpassword?email&hashcode',
      templateUrl: 'templates/common/reset-password-form.html',
      controller: 'ForgotPasswordController',
      resolve: {
        loadFiles: ['$ocLazyLoad', function ($ocLazyLoad) {
          return $ocLazyLoad.load(['js/components/controller/common/ForgotPasswordController.js']);
        }]
      }
    })

    .state('accountSetup',
    {
      url: '/accountsetup?email&hashcode',
      templateUrl: 'templates/common/account-setup.html',
      controller: 'AccountSetupController',
      resolve: {
        loadFiles: ['$ocLazyLoad', function ($ocLazyLoad) {
          return $ocLazyLoad.load(['js/components/controller/common/AccountSetupController.js']);
        }]
      }
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
	    controller: 'FacultyController',
      resolve: {
        loadFiles: ['$ocLazyLoad', function ($ocLazyLoad) {          
          return $ocLazyLoad.load(['luegg.directives','checklist-model','js/components/controller/faculty/FacultyController.js']);              
        }]
      }
	  })

    .state('faculty.referralForm',
    {
      url: '/referral/form',
      templateUrl: 'templates/faculty/referral-form.html',
      loginRequired: true,
      accessLevel: 3,
      controller: 'ReferralFormController',
      resolve: {
        loadFiles: ['$ocLazyLoad', function ($ocLazyLoad) {
          return $ocLazyLoad.load(['js/components/controller/faculty/ReferralFormController.js']);
        }]
      }
    })

    .state('faculty.referralHistory',
    {
      url: '/referral/history',
      templateUrl: 'templates/faculty/referral-history.html',
      loginRequired: true,
      accessLevel: 3,
      controller: 'ReferralHistoryController',
      resolve: {
        loadFiles: ['$ocLazyLoad', function ($ocLazyLoad) {
          return $ocLazyLoad.load(['js/components/controller/faculty/ReferralHistoryController.js']);
        }]
      }
    })

    .state('faculty.messaging',
    {
      url: '/messaging/faculty',
      templateUrl: 'templates/faculty/messaging.html',
      loginRequired: true,
      accessLevel: 3,
      controller: 'MessagesController',
      resolve: {
        loadFiles: ['$ocLazyLoad', function ($ocLazyLoad) {
          return $ocLazyLoad.load(['js/components/controller/common/MessagesController.js']);
        }]
      }
    })

    .state('faculty.settings',
    {
      url: '/settings/faculty',
      templateUrl: 'templates/faculty/settings.html',
      loginRequired: true,
      accessLevel: 3,
      controller: 'SettingsController',
      resolve: {
        loadFiles: ['$ocLazyLoad', function ($ocLazyLoad) {
          return $ocLazyLoad.load(['js/components/controller/common/SettingsController.js']);
        }]
      }
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
      accessLevel: 2,
      resolve: {
        loadFiles: ['$ocLazyLoad', function ($ocLazyLoad) {          
          return $ocLazyLoad.load(['chart.js','luegg.directives','checklist-model','lib/date.js','lib/pdf/jspdf.min.js','js/components/controller/admin/AdminController.js']);              
        }]
      }
	  })

    .state('admin.reports',
    {
      url: '/reports',
      templateUrl: 'templates/admin/reports.html',
      controller: 'ReportsController',
      loginRequired: true,
      accessLevel: 2,
      resolve: {
        loadFiles: ['$ocLazyLoad', function ($ocLazyLoad) {
          return $ocLazyLoad.load(['js/components/controller/admin/ReportsController.js']);
        }]
      }
    })

    .state('admin.messaging',
    {
      url: '/messaging/admin',
      templateUrl: 'templates/admin/messaging.html',
      controller: 'MessagesController',
      loginRequired: true,
      accessLevel: 2,
      resolve: {
        loadFiles: ['$ocLazyLoad', function ($ocLazyLoad) {
          return $ocLazyLoad.load(['js/components/controller/common/MessagesController.js']);
        }]
      }
    })

    .state('admin.manageFaculty',
    {
      url: '/manage/faculty',
      templateUrl: 'templates/admin/manage-faculty.html',
      controller: 'ManageFacultyController',
      loginRequired: true,
      accessLevel: 2,
      resolve: {
        loadFiles: ['$ocLazyLoad', function ($ocLazyLoad) {
          return $ocLazyLoad.load(['js/components/controller/admin/ManageFacultyController.js']);
        }]
      }
    })

    .state('admin.manageStudents',
    {
      url: '/manage/students',
      templateUrl: 'templates/admin/manage-students.html',
      controller: 'ManageStudentController',
      loginRequired: true,
      accessLevel: 2,
      resolve: {
        loadFiles: ['$ocLazyLoad', function ($ocLazyLoad) {
          return $ocLazyLoad.load(['js/components/controller/admin/ManageStudentController.js']);
        }]
      }
    })

    .state('admin.manageDatabase',
    {
      url: '/manage/database',
      templateUrl: 'templates/admin/manage-database.html',
      controller: 'DatabaseController',
      loginRequired: true,
      accessLevel: 2,
      resolve: {
        loadFiles: ['$ocLazyLoad', function ($ocLazyLoad) {
          return $ocLazyLoad.load(['js/components/controller/admin/DatabaseController.js']);
        }]
      }
    })

    .state('admin.summary',
    {
      url: '/summary',
      templateUrl: 'templates/admin/summary.html',
      controller: 'SummaryController',
      loginRequired: true,
      accessLevel: 2,
      resolve: {
        loadFiles: ['$ocLazyLoad', function ($ocLazyLoad) {
          return $ocLazyLoad.load(['js/components/controller/admin/SummaryController.js']);
        }]
      }
    })

    .state('admin.settings',
    {
      url: '/settings/admin',
      templateUrl: 'templates/admin/settings.html',
      controller: 'SettingsController',
      loginRequired: true,
      accessLevel: 2,
      resolve: {
        loadFiles: ['$ocLazyLoad', function ($ocLazyLoad) {
          return $ocLazyLoad.load(['js/components/controller/common/SettingsController.js']);
        }]
      }
    })

    .state('admin.404Inside',
    {
      templateUrl: 'templates/common/404-inside.html'
    })


    //SUPERADMIN
    //<-------------------------------------------------------------------------->


	  .state('superadmin',
    {
	    templateUrl: 'templates/superadmin/superadmin.html',
	    controller: 'SuperAdminController',
      loginRequired: true,
      accessLevel: 1,
      resolve: {
        loadFiles: ['$ocLazyLoad', function ($ocLazyLoad) {          
          return $ocLazyLoad.load(['luegg.directives','checklist-model','js/components/controller/superadmin/SuperAdminController.js']);              
        }]
      }
	  })

    .state('superadmin.manageAdmin',
    {
      url: '/manage/admin',
      templateUrl: 'templates/superadmin/manage-admin.html',
      controller: 'ManageAdminController',
      loginRequired: true,
      accessLevel: 1,
      resolve: {
        loadFiles: ['$ocLazyLoad', function ($ocLazyLoad) {
          return $ocLazyLoad.load(['js/components/controller/superadmin/ManageAdminController.js']);
        }]
      }
    })

    .state('superadmin.settings',
    {
      url: '/settings/superadmin',
      templateUrl: 'templates/superadmin/settings.html',
      controller: 'SettingsController',
      loginRequired: true,
      accessLevel: 1,
      resolve: {
        loadFiles: ['$ocLazyLoad', function ($ocLazyLoad) {
          return $ocLazyLoad.load(['js/components/controller/common/SettingsController.js']);
        }]
      }
    })

    .state('superadmin.404Inside',
    {
      templateUrl: 'templates/common/404-inside.html'
    })


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


.service('AuthService', function ($q, $http, $localStorage, config, $state)
{
  var email = '';
  var role = '';
  var name = '';
  var department = '';

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
    department = '';
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

  this.getDepartment = function ()
  {
    if(getPayLoad())
    {
      department = getPayLoad().data.department;
    }

    return department
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
    $state.go('login');
  }
})


// <------------------------------------------------------------------>


.run(function ($rootScope, $localStorage, $state, AuthService, AUTH_EVENTS, $transitions)
{
  $rootScope.$on(AUTH_EVENTS.notAuthenticated, function(event)
  {
    event.preventDefault();
    AuthService.logout();   
  })

  $rootScope.$on(AUTH_EVENTS.notAuthorized, function(event)
  {
    event.preventDefault();
    $state.go('login');
  })

  $transitions.onStart({}, function($transitions)
  {
    $state.defaultErrorHandler(function(){});

    if ($transitions.$to().loginRequired && !$localStorage.currentUser)
    {     
      AuthService.logout();
    }

    if(($localStorage.currentUser && $transitions.$to().accessLevel == 0) || ($transitions.$to().loginRequired && AuthService.getRole() != $transitions.$to().accessLevel))
    {
      //event.preventDefault();
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
