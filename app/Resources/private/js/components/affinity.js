/* ===========================================================================
   Affinity elements
   =========================================================================== */

import Chart from 'chart.js';

/** Affinty Radar Chart **/

const $radarChart = $('.js-radar-chart');

if ($radarChart.length) {
  $radarChart.each((index, element) => {
    let myRadarChart = new Chart(element, {
      type: 'radar',
      data: $(element).data('dataset'),
      options: {
        scale: {
          ticks: {
            beginAtZero : true,
            max: 100
          }
        }
      }
    });
  });
}