
function openTab(evt, tabName, coin) {
  // Declare all variables
  var i, tabcontent, tablinks;

  // Get all elements with class="tabcontent" and hide them (always show first)
  tabcontent = document.getElementsByClassName("tabcontent");
  for (i = 0; i < tabcontent.length; i++) {
    tabcontent[i].style.display = "none";
  }

  // Get all elements with class="tablinks" and remove the class "active"
  tablinks = document.getElementsByClassName("tablinks");
  for (i = 0; i < tablinks.length; i++) {
    tablinks[i].className = tablinks[i].className.replace(" active", "");
    tablinks[i].className = tablinks[i].className.replace(coin, "");
  }

  // Show the current tab, and add an "active" class to the button that opened the tab
  document.getElementById(tabName).style.display = "block";
  evt.currentTarget.className += " active " + coin;
}

/**
 * Plots line graph
 */
function plotLineGraph(json, element_id, label) {
  // Load the fonts
  Highcharts.createElement('link', {
    href: 'https://fonts.googleapis.com/css?family=Dosis:400,600',
    rel: 'stylesheet',
    type: 'text/css'
  }, null, document.getElementsByTagName('head')[0]);

  Highcharts.theme = {
    colors: ['#7cb5ec', '#f7a35c', '#90ee7e', '#7798BF', '#aaeeee', '#ff0066', '#eeaaee',
      '#55BF3B', '#DF5353', '#7798BF', '#aaeeee'],
    chart: {
      backgroundColor: '#eeeeee',
      style: {
        fontFamily: 'Dosis, sans-serif'
      }
    },
    title: {
      style: {
        fontSize: '16px',
        fontWeight: 'bold',
        textTransform: 'uppercase'
      }
    },
    tooltip: {
      borderWidth: 0,
      backgroundColor: 'rgba(219,219,216,0.8)',
      shadow: false
    },
    legend: {
      itemStyle: {
        fontWeight: 'bold',
        fontSize: '13px'
      }
    },
    xAxis: {
      gridLineWidth: 1,
      labels: {
        style: {
          fontSize: '12px'
        }
      }
    },
    yAxis: {
      minorTickInterval: 'auto',
      title: {
        style: {
          textTransform: 'uppercase'
        }
      },
      labels: {
        style: {
          fontSize: '12px'
        }
      }
    },
    plotOptions: {
      candlestick: {
        lineColor: '#404048'
      }
    },

    // General
    background2: '#F0F0EA'

  };

  // Apply the theme
  Highcharts.setOptions(Highcharts.theme);

  // Default setups
  var c_series = {
    dataLabels: {
      align: 'left',
      enabled: false
    }
  }

  var c_tooltip = {
    shared: true,
    crosshairs: true
  };

  if (element_id == 'container-block-diff') {
    var c_tooltip = {
      formatter: function () {
        return "Block height: " + this.x + "<br />" + " Difficulty: " + this.y;
      },
      shared: true,
      crosshairs: true
    };

    var c_series = {
      step: 'left',
      dataLabels: {
        align: 'left',
        enabled: true
      }
    }
  }

  Highcharts.chart(element_id, {

    data: {
      csv: json
    },

    title: {
      text: label
    },

    subtitle: {
      text: null
    },

    xAxis: {
      //tickAmount: 24,
      //tickInterval: 24 / 12,
      //minTickInterval: 3600,
      //dateTimeLabelFormats : {
      //  day: '%H:%M'
      //},
      tickWidth: 0,
      gridLineWidth: 1,
      labels: {
        align: 'left',
        x: 3,
        y: -3
      },
      labels: {
        rotation: 0,
        step: 24 // show every tick regardless of spacing
      },
      //startOnTick: false,
      //endOnTick: true,
      showLastLabel: true
    },

    yAxis: [{ // left y axis
      title: {
        text: null
      },
      labels: {
        align: 'left',
        x: 3,
        y: 16,
        format: '{value:.,0f}'
      },
      showFirstLabel: false
    }, { // right y axis
      linkedTo: 0,
      gridLineWidth: 0,
      opposite: true,
      title: {
        text: null
      },
      labels: {
        align: 'right',
        x: -3,
        y: 16,
        format: '{value:.,0f}'
      },
      showFirstLabel: false
    }],

    legend: {
      enabled: false,
      align: 'left',
      verticalAlign: 'top',
      y: 10,
      floating: true,
      borderWidth: 0
    },

    tooltip: c_tooltip,

    plotOptions: {
      series: c_series,
    },

    series: [{
      name: 'Hashrate',
      lineWidth: 4,
      marker: {
        radius: 4
      }
    }]
  });
}

/**
 * JS clicks for tabs
 */
if (document.getElementById('defaultOpen') !== null) {
  // Open workers tab by default
  document.getElementById("defaultOpen").click();
}

