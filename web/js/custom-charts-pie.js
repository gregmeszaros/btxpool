
/**
 * Plots charts graph
 */
function plotPieGraph(data, element_id, label) {

  var pseriesData = [];
  var data = data.split('\n');
  //var seriesData = [];
  $.each(data, function(itemRow, item) {
    if (itemRow > 0) {
      var items = item.split(',');
      if (items.length > 1) {
        pseriesData.push({name: items[0], y: parseFloat(items[1])});
      }
    }
  });

  Highcharts.chart(element_id, {
    chart: {
      plotBackgroundColor: null,
      plotBorderWidth: null,
      plotShadow: false,
      type: 'pie'
    },
    title: {
      text: label
    },
    tooltip: {
      pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
    },
    plotOptions: {
      pie: {
        allowPointSelect: true,
        cursor: 'pointer',
        dataLabels: {
          enabled: false,
          format: '<b>{point.name}</b>: {point.percentage:.1f} %',
          style: {
            color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
          }
        },
        showInLegend: true
      }
    },
    series: [{
      name: 'Percentage of total active workers',
      colorByPoint: true,
      data: pseriesData
    }]
  });
}