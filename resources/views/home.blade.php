@extends('layouts.app_1')

@section('content')

<main id="main" class="main">

  <div class="pagetitle">
    <h1>Dashboard</h1>
  </div><!-- End Page Title -->

  <section class="section dashboard">
    <div class="row">

      <!-- Left side columns -->
      <div class="col-lg-12">
        <div class="row">

          <!-- Total Tickets Card -->
          <div class="col-md-3">
            <div class="card info-card sales-card">
              <div class="card-body">
                <h5 class="card-title">Total Tickets</h5>
                <div class="d-flex align-items-center">
                  <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                    <i class="bi bi-ticket-detailed"></i>
                  </div>
                  <div class="ps-3">
                    <h6>{{ $totalTickets ?? 0 }}</h6>
                  </div>
                </div>
              </div>
            </div>
          </div><!-- End Total Tickets Card -->

          <!-- Open Tickets Card -->
          <div class="col-md-3">
            <div class="card info-card revenue-card">
              <div class="card-body">
                <h5 class="card-title">Open Tickets</h5>
                <div class="d-flex align-items-center">
                  <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                    <i class="bi bi-folder2-open"></i>
                  </div>
                  <div class="ps-3">
                    <h6>{{ $openTickets ?? 0 }}</h6>
                  </div>
                </div>
              </div>
            </div>
          </div><!-- End Open Tickets Card -->

          <!-- In Progress Tickets Card -->
          <div class="col-md-3">
            <div class="card info-card warning-card">
              <div class="card-body">
                <h5 class="card-title">In Progress</h5>
                <div class="d-flex align-items-center">
                  <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                    <i class="bi bi-hourglass-split"></i>
                  </div>
                  <div class="ps-3">
                    <h6>{{ $inProgressTickets ?? 0 }}</h6>
                  </div>
                </div>
              </div>
            </div>
          </div><!-- End In Progress Tickets Card -->

          <!-- Closed Tickets Card -->
          <div class="col-md-3">
            <div class="card info-card customers-card">
              <div class="card-body">
                <h5 class="card-title">Closed Tickets</h5>
                <div class="d-flex align-items-center">
                  <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                    <i class="bi bi-patch-check"></i>
                  </div>
                  <div class="ps-3">
                    <h6>{{ $closedTickets ?? 0 }}</h6>
                  </div>
                </div>
              </div>
            </div>
          </div><!-- End Closed Tickets Card -->

        </div>
      </div><!-- End Left side columns -->

      <!-- Right side columns (if any, can be removed or used for other info) -->
      <!-- <div class="col-lg-4"> -->
      <!-- Content for right side -->
      <!-- </div> -->
      <!-- End Right side columns -->

    </div>

    <div class="row">
      <div class="col-lg-12">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Ticket Status Distribution</h5>
            <div id="ticketStatusChart"
              style="min-height: 400px; position: relative; border: 1px solid #eee; border-radius: 5px;"></div>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-lg-12">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Ticket Trends (Last 14 Days)</h5>
            <div id="ticketTrendChart"
              style="min-height: 400px; position: relative; border: 1px solid #eee; border-radius: 5px;"></div>
          </div>
        </div>
      </div>
    </div>

    @if($role == 'Admin')
    <div class="row">
      <div class="col-lg-6">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Tickets by Admin Assignment</h5>
            <div id="ticketAssignmentChart"
              style="min-height: 400px; position: relative; border: 1px solid #eee; border-radius: 5px;"></div>
          </div>
        </div>
      </div>

      <div class="col-lg-6">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Ticket Age Distribution</h5>
            <div id="ticketAgeChart"
              style="min-height: 400px; position: relative; border: 1px solid #eee; border-radius: 5px;"></div>
          </div>
        </div>
      </div>
    </div>
    @endif

  </section>
  <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i
      class="bi bi-arrow-up-short"></i></a>
</main><!-- End #main -->

<style>
  .warning-card .card-icon {
    background: #ffecb5;
    color: #ff9800;
  }

  .warning-card h6 {
    color: #ff9800;
    font-weight: 700;
  }
</style>

<script>
  document.addEventListener("DOMContentLoaded", () => {
    // Initialize status distribution pie chart
    const chartElement = document.querySelector("#ticketStatusChart");
    
    if (typeof ApexCharts !== 'undefined') {
      const chart = new ApexCharts(chartElement, {
        series: [
          {{ $openTickets ?? 0 }}, 
          {{ $inProgressTickets ?? 0 }}, 
          {{ $closedTickets ?? 0 }}
        ],
        chart: {
          height: 350,
          type: 'pie',
          toolbar: {
            show: true
          }
        },
        labels: ['Open Tickets', 'In Progress', 'Closed Tickets'],
        colors: ['#00C292', '#FFB848', '#0275D8']
      });
      
      chart.render();
      
      // Initialize ticket trend line chart
      const trendChartElement = document.querySelector("#ticketTrendChart");
      
      const trendChart = new ApexCharts(trendChartElement, {
        series: [
          {
            name: 'New Tickets',
            data: [{{ implode(',', $createdTickets ?? []) }}]
          },
          {
            name: 'Closed Tickets',
            data: [{{ implode(',', $closedTicketsData ?? []) }}]
          }
        ],
        chart: {
          height: 350,
          type: 'line',
          zoom: {
            enabled: false
          },
          toolbar: {
            show: true
          }
        },
        dataLabels: {
          enabled: false
        },
        stroke: {
          curve: 'smooth',
          width: 3
        },
        colors: ['#FF5733', '#0275D8'],
        grid: {
          row: {
            colors: ['#f3f3f3', 'transparent'],
            opacity: 0.5
          }
        },
        markers: {
          size: 5
        },
        xaxis: {
          categories: ['{!! implode("','", $dateRange ?? []) !!}'],
          title: {
            text: 'Date'
          }
        },
        yaxis: {
          title: {
            text: 'Number of Tickets'
          },
          min: 0,
          forceNiceScale: true
        },
        legend: {
          position: 'top',
          horizontalAlign: 'right'
        },
        tooltip: {
          shared: true,
          intersect: false,
          y: {
            formatter: function (y) {
              if (typeof y !== "undefined") {
                return y.toFixed(0) + " tickets";
              }
              return y;
            }
          }
        }
      });
      
      trendChart.render();

      @if($role == 'Admin')
      // Initialize ticket assignment bar chart
      const assignmentChartElement = document.querySelector("#ticketAssignmentChart");
      
      const assignmentChart = new ApexCharts(assignmentChartElement, {
        series: [{
          name: 'Tickets Assigned',
          data: [{{ implode(',', $ticketsByUser ?? []) }}]
        }],
        chart: {
          type: 'bar',
          height: 350,
          toolbar: {
            show: true
          }
        },
        plotOptions: {
          bar: {
            horizontal: true,
            dataLabels: {
              position: 'top',
            },
          }
        },
        colors: ['#6f42c1'],
        dataLabels: {
          enabled: true,
          offsetX: -6,
          style: {
            fontSize: '12px',
            colors: ['#fff']
          }
        },
        stroke: {
          show: true,
          width: 1,
          colors: ['#fff']
        },
        grid: {
          borderColor: '#e7e7e7',
          row: {
            colors: ['#f3f3f3', 'transparent'],
            opacity: 0.5
          }
        },
        xaxis: {
          categories: ['{!! implode("','", $userNames ?? []) !!}'],
          title: {
            text: 'Number of Tickets'
          }
        },
        yaxis: {
          title: {
            text: 'Admin Users'
          }
        }
      });
      
      assignmentChart.render();
      
      // Initialize ticket age chart
      const ageChartElement = document.querySelector("#ticketAgeChart");
      
      const ageChart = new ApexCharts(ageChartElement, {
        series: [{
          name: 'Tickets',
          data: [{{ implode(',', $ticketAgeCounts ?? []) }}]
        }],
        chart: {
          type: 'bar',
          height: 350,
          toolbar: {
            show: true
          }
        },
        plotOptions: {
          bar: {
            borderRadius: 4,
            distributed: true,
            dataLabels: {
              position: 'top',
            },
          }
        },
        colors: ['#00C292', '#FFB848', '#FF5733', '#DC3545'],
        dataLabels: {
          enabled: true,
          formatter: function (val) {
            return val;
          },
          offsetY: -20,
          style: {
            fontSize: '12px',
            colors: ["#304758"]
          }
        },
        xaxis: {
          categories: ['{!! implode("','", $ticketAgeCategories ?? []) !!}'],
          position: 'bottom',
          axisBorder: {
            show: false
          },
          axisTicks: {
            show: false
          },
          title: {
            text: 'Ticket Age'
          }
        },
        yaxis: {
          title: {
            text: 'Number of Tickets'
          }
        }
      });
      
      ageChart.render();
      @endif
    } else {
      console.error("ApexCharts is not defined. Please ensure the library is properly loaded.");
      chartElement.innerHTML = '<div class="alert alert-warning">Chart library not loaded. Please refresh the page.</div>';
    }
  });
</script>
@endsection

@push('scripts')
<!-- This was previously here but doesn't appear to be used since there's no @stack('scripts') in the layout -->
@endpush