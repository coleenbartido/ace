//DIRECTIVE MODULE
angular.module('aceWeb')


//<-------------------------------DIRECTIVE---------------------------------------------------->


.directive('validateEmail', function() {
  var pattern = /^[-a-z0-9~!$%^&*_=+}{\'?]+(\.[-a-z0-9~!$%^&*_=+}{\'?]+)*@([a-z0-9_][-a-z0-9_]*(\.[-a-z0-9_]+[a-z][a-z])|([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}))(:[0-9]{1,5})?$/i;

  return {
    require: 'ngModel',
    restrict: '',
    link: function(scope, elm, attrs, ctrl) {
      // only apply the validator if ngModel is present and Angular has added the email validator
      if (ctrl && ctrl.$validators.email) {
        // this will overwrite the default Angular email validator
        ctrl.$validators.email = function(modelValue) {
          return ctrl.$isEmpty(modelValue) || pattern.test(modelValue);
        };
      }
    }
  };
})


// <----------------------------------------------------------------------------------->


.directive('autoFocus', function($timeout) {
    return {
        restrict: 'AC',
        link: function(_scope, _element) {
            $timeout(function(){
                _element[0].focus();
            }, 0);
        }
    };
})


// <----------------------------------------------------------------------------------->


.directive('noclick', function() {
    return {
      restrict: 'A',
      link: function link(scope, element, attrs) {
        element.bind('click', function(e) {
            e.stopPropagation();
        });
      }
    }
})


// <----------------------------------------------------------------------------------->


.directive("limitTo", [function() {
    return {
        restrict: "A",
        link: function(scope, elem, attrs) {
            var limit = parseInt(attrs.limitTo);
            angular.element(elem).on("keypress", function(e) {
                if (this.value.length == limit) e.preventDefault();
            });
        }
    }
}])


// <----------------------------------------------------------------------------------->


.directive("loadMoreData", [function() {
    return {
      restrict: 'EA',
      link: function($scope, element, attrs) {
        var raw = element[0];
        element.scroll(function() {
          if(raw.scrollTop == 0) {
            $scope.scrollOnTop = true;
          }
          if(raw.scrollTop <= 30 && !$scope.scrollOnTop) {
            var currentScrollHeight = raw.scrollHeight;
            $scope.$apply("incrementShowLimit()");
            raw.scrollTop = raw.scrollHeight - currentScrollHeight;
          }
        });
      }
    };
}])


// <----------------------------------------------------------------------------------->


.directive("compareTo", [function() {
  return {
    require: "ngModel",
    scope:
    {
      otherModelValue: "=compareTo"
    },
    link: function(scope, element, attributes, ngModel) {

      ngModel.$validators.compareTo = function(modelValue) {
        return modelValue == scope.otherModelValue;
      };

      scope.$watch("otherModelValue", function() {
        ngModel.$validate();
      });
    }
  };
}])


// <----------------------------------------------------------------------------------->


.directive('fileModel', ['$parse', function ($parse) {
    return {
    restrict: 'A',
    link: function(scope, element, attrs) {
        var model = $parse(attrs.fileModel);
        var modelSetter = model.assign;

        element.bind('change', function(){
            scope.$apply(function(){
                modelSetter(scope, element[0].files[0]);
            });
        });
    }
   };
}])

























//<-------------------------------FILTER---------------------------------------------------->


.filter('abs', function () {
  return function(val) {
    return Math.abs(val);
  }
})


//<--------------------------------------------------------------------------------->


.filter('newline2br', function($sce) {
  var span = document.createElement('span');
    return function(input) {
        if (!input) return input;
        var lines = input.split('\n');

        for (var i = 0; i < lines.length; i++) {
            span.innerText = lines[i];
            span.textContent = lines[i]; //for Mozilla Firefox
            lines[i] = span.innerHTML;
        }
        //return lines.join('<br />');
        return $sce.trustAsHtml(lines.join('<br />'));
    }
})


//<--------------------------------------------------------------------------------->


.filter('startFrom', function () {
  return function (input, start) {
    if (input) {
      start = +start;
      return input.slice(start);
    }
    return [];
  };
})
