angular.module('aceWeb')


.controller('SummaryController', function(config, $scope, $http, AuthService)
{
  //init function which will retrieve list of school year in the database
  $scope.getSY = function()
  {
    var userDetails =
    {
      'email' : AuthService.getEmail()
    }
    
    $http({
      method: 'POST',
      url: config.apiUrl + '/auth/getSYList',
      data: userDetails,
      headers: {'Content-Type': 'application/x-www-form-urlencoded'}
    })
    .then(function(response)
    {
      //for checking
      console.log(response);

      $scope.SYList = JSON.parse(response.data.SYList);

      if($scope.SYList.length != 0)
      {
        $scope.selectedSY = $scope.SYList[$scope.SYList.length-1].school_year;

        $scope.getSummaryData();
      }
      else
      {
        $scope.isEmptySYList = true;
      }
      $scope.isLoading = false;
    },
    function(response)
    {

    })
    .finally(function()
    {

    });
  }

  //init function which will retrieve all the data in rendering the summary chart
  $scope.getSummaryData = function()
  {
    $scope.isLoadingChart = true;

    var userDetails =
    {
      'email' : AuthService.getEmail(),
      'schoolYear' : $scope.selectedSY
    }

    $http({
      method: 'POST',
      url: config.apiUrl + '/auth/getChartData',
      data: userDetails,
      headers: {'Content-Type': 'application/x-www-form-urlencoded'}
    })
    .then(function(response)
    {
      //for checking
      console.log(response);

      var dept = response.data.department;
      var termDataObj = JSON.parse(response.data.termData);
      var programDataObj = JSON.parse(response.data.programData);
      var levelDataObj = JSON.parse(response.data.levelData);
      var reasonDataObj = JSON.parse(response.data.reasonData);
      var statusDataObj = JSON.parse(response.data.statusData);

      //color of bars
      $scope.datasetOverride = [{ backgroundColor: ['#10326F','#10326F','#10326F','#10326F','#10326F','#10326F','#10326F','#10326F','#10326F'] }];
   
      //labels
      if(dept == 1)
      {
        $scope.programLabels = [['','Humanities And','Social Sciences'],['','Accountancy Business','And Management'],['','Computer','Programming'],'Animation','Fashion Design','Multimedia Arts'];
        $scope.levelLabels = ['Grade 11','Grade 12'];
        $scope.termLabels = ['First Term','Second Term'];
      }
      else
      {
        $scope.programLabels = [['','Software','Engineering'],['','Game','Development'],['','Web','Development'],'Animation',['','Multimedia','Arts And Design'],['','Fashion','Design'],['','Real Estate','Management'],['','Business','Administration']];
        $scope.levelLabels = ['First Year','Second Year','Third Year','Fourth Year'];
        $scope.termLabels = ['First Term','Second Term','Third Term'];
      }
    
      $scope.reasonLabels = ['Absent or Late','Underachieving','Failing','Plans to Transfer','Violent/Disruptive','Emotional Distress','Others'];
      $scope.statusLabels = ['Uncounseled','In Progress','Counseled'];

      //data
      $scope.termData = [[]];
      $scope.programData = [[]];
      $scope.levelData = [[]];
      $scope.reasonData = [[]];
      $scope.statusData = [[]];

      convertToArray(termDataObj[0], $scope.termData[0]);
      convertToArray(programDataObj[0], $scope.programData[0]);
      convertToArray(levelDataObj[0], $scope.levelData[0]);
      convertToArray(reasonDataObj[0], $scope.reasonData[0]);
      convertToArray(statusDataObj[0], $scope.statusData[0]);

      //series
      $scope.series = ['Number of reports'];

      //options
      $scope.termOptions = getOption("Term", $scope.termData[0]);
      $scope.programOptions = getOption("Program", $scope.programData[0]);
      $scope.levelOptions = getOption("Level", $scope.levelData[0]);
      $scope.reasonOptions = getOption("Reason", $scope.reasonData[0]);
      $scope.statusOptions = getOption("Status", $scope.statusData[0]);
    },
    function(response)
    {

    })
    .finally(function()
    {
      $scope.isLoadingChart = false;
    });
  }

  $scope.initScope = function()
  {
    $scope.isLoading = true;
    $scope.isLoadingChart = false;
    $scope.currentDate = Date.today().toString('dddd, MMMM d, yyyy');
    $scope.currentDateNum = Date.today().toString('MMddyy');
    $scope.isEmptySYList = false;

    $scope.getSY();

    Chart.plugins.register({
      beforeDraw: function(chartInstance) 
      {
        var ctx = chartInstance.chart.ctx;
        ctx.fillStyle = "white";
        ctx.fillRect(0, 0, chartInstance.chart.width, chartInstance.chart.height);
      }
    });
  } //scope initScope

  $scope.initScope();

  //user defined functions
  function convertToArray(obj, scopeArr)
  {
    angular.forEach(obj, function(value, key)
    {
      scopeArr.push(value);
    });
  }

  function arrayMax(array)
  {
    return array.reduce(function(a, b) {
      return Math.max(a, b);
    });
  }

  function getXOffset(doc, str)
  {
    return strOffset = (doc.internal.pageSize.width / 2) - (doc.getStringUnitWidth(str) * doc.internal.getFontSize() / 6);
  }

  function getOption(chartName, dataArr)
  {
    var option =
    {
        scales:
        {
          yAxes:
          [{
              id: 'y-axis-1',
              type: 'linear',
              display: true,
              position: 'left',
              ticks:
              {
                min: 0, max: (Math.ceil(arrayMax(dataArr) / 10) * 10) + 30, stepSize: 10
              },
              scaleLabel:
              {
                display: true,
                labelString: 'Number of Reports'
              }
          }]
        },
        title:
        {
          display: true,
          text: 'Reports Per ' + chartName,
          padding: 30,
          fontSize: 30,
          fontFamily: 'gotham-book'
        }
        ,
        tooltips:
        {
          enabled: true,
          mode: 'single',
          callbacks: {
            title: function(tooltipItems, data) {
              return '';
            }
          }
        }
      };
      return option;
  }

  //download image function
  $scope.downloadImage = function(event, chartId)
  {
    var sy = $scope.selectedSY.split(/\s*-\s*/);
    var elemRef = document.getElementById(event.target.id);

    var oldCanvas = document.getElementById(chartId);

    var canvas = document.getElementById(chartId + '2');
    var context = canvas.getContext('2d');

    var image = document.getElementById('imgForChart');

    canvas.width = oldCanvas.width;
    canvas.height = oldCanvas.height * 1.30;

    context.fillStyle = "white";
    context.fillRect(0, 0, canvas.width, canvas.height); 

    context.drawImage(oldCanvas, 0, canvas.height * 0.19); 
    
    context.drawImage(image, canvas.width * 0.335, canvas.height * 0.048, canvas.height * 0.08, canvas.height * 0.08);
    
    context.font = 'bold ' + (canvas.height * 0.06) + 'pt Arial';
    context.fillStyle = 'black';
    var headerText = 'iACADEMY';
    context.fillText(headerText, canvas.width * 0.39, canvas.height * 0.12);

    context.font = 'bold ' + (canvas.height * 0.025) + 'pt Arial';
    context.fillStyle = 'black';
    context.fillText($scope.currentDate, canvas.width * 0.40, canvas.height * 0.18);

    elemRef.href = canvas.toDataURL('image/png', 1.0);
    elemRef.download = chartId + '_chart_' + $scope.currentDateNum + '_' + sy[0] + sy[1] + '.png';
  }

  //download pdf function
  $scope.downloadPDF = function(chartId)
  {    
    var sy = $scope.selectedSY.split(/\s*-\s*/);

    var oldCanvas = document.getElementById(chartId);

    var canvas = document.getElementById(chartId + '2');
    var context = canvas.getContext('2d');

    var image = document.getElementById('imgForChart');

    canvas.width = oldCanvas.width;
    canvas.height = oldCanvas.height * 1.30;

    context.fillStyle = "white";
    context.fillRect(0, 0, canvas.width, canvas.height); 

    context.drawImage(oldCanvas, 0, canvas.height * 0.19); 
    
    context.drawImage(image, canvas.width * 0.335, canvas.height * 0.048, canvas.height * 0.08, canvas.height * 0.08);
    
    context.font = 'bold ' + (canvas.height * 0.06) + 'pt Arial';
    context.fillStyle = 'black';
    var headerText = 'iACADEMY';
    context.fillText(headerText, canvas.width * 0.39, canvas.height * 0.12);

    context.font = 'bold ' + (canvas.height * 0.025) + 'pt Arial';
    context.fillStyle = 'black';
    context.fillText($scope.currentDate, canvas.width * 0.40, canvas.height * 0.18);

    var myImage = canvas.toDataURL('image/jpeg', 1.0);

    var doc = new jsPDF('landscape');
        
    doc.addImage(myImage, 'JPEG', 23, 15, 250, 160);
    doc.save(chartId + '_chart_' + $scope.currentDateNum + '_' + sy[0] + sy[1] + '.pdf');  
  }

})
