angular.module('aceWeb')


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
