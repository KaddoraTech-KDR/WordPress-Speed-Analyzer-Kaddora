jQuery(document).ready(function ($) {
  // store last report
  let lastReport = null;

  // scan run
  $("#wsa-run-audit").on("click", function () {
    $("#wsa-results").html('<div class="wsa-loading">Scanning...</div>');

    fetch(wsaData.restUrl, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-WP-Nonce": wsaData.nonce,
      },
    })
      .then((res) => res.json())
      .then((data) => {
        if (!data.success) {
          $("#wsa-results").html(
            "<p style='color:red'>" + data.message + "</p>",
          );
          return;
        }

        let d = data.data;

        // Save for export
        lastReport = d;

        // Score Color
        let scoreClass = "wsa-green";
        if (d.score < 50) scoreClass = "wsa-red";
        else if (d.score < 80) scoreClass = "wsa-orange";

        // Suggestions
        let suggestionsHtml = "";
        if (d.suggestions.length > 0) {
          suggestionsHtml = `
            <div class="wsa-card">
              <h3>Suggestions</h3>
              <ul class="wsa-list">
                ${d.suggestions.map((s) => `<li>${s}</li>`).join("")}
              </ul>
            </div>
          `;
        }

        // Plugins
        let pluginHtml = `
          <div class="wsa-card">
            <h3>Plugins</h3>
            <p>Total: ${d.plugins.total_plugins}</p>
            <p>Heavy: ${d.plugins.heavy_plugins.length}</p>
            <ul class="wsa-list">
              ${d.plugins.heavy_plugins.map((p) => `<li>${p}</li>`).join("")}
            </ul>
          </div>
        `;

        // Database
        let dbHtml = `
          <div class="wsa-card">
            <h3>Database</h3>
            <p>Size: ${d.database.total_size}</p>
            <p>Autoload: ${d.database.autoload_size}</p>
            <p>Revisions: ${d.database.revisions}</p>
          </div>
        `;

        // Reports
        let reportsHtml = "";
        if (d.reports.length > 0) {
          reportsHtml = `
            <div class="wsa-card">
              <h3>Previous Reports</h3>
              <ul class="wsa-list">
                ${d.reports
                  .map(
                    (r) => `
                  <li>${r.time} → Score: ${r.data.score}</li>
                `,
                  )
                  .join("")}
              </ul>
            </div>
          `;
        }

        // grath
        let recentReports = [];
        if (d.reports && d.reports.length > 0) {
          recentReports = d.reports.slice(-6);
        }

        // main ui
        $("#wsa-results").html(`
          <div class="wsa-cards">

            <div class="wsa-card ${scoreClass}">
              <h3>Performance Score</h3>
              <p class="wsa-score ${scoreClass}">${d.score}</p>
            </div>

            <div class="wsa-card">
              <h3>LCP</h3>
              <p>${d.lcp}</p>
            </div>

            <div class="wsa-card">
              <h3>CLS</h3>
              <p>${d.cls}</p>
            </div>

            <div class="wsa-card">
              <h3>FID</h3>
              <p>${d.fid}</p>
            </div>

            <div class="wsa-card">
              <h3>Speed Index</h3>
              <p>${d.speed_index}</p>
            </div>

            ${pluginHtml}
            ${dbHtml}
            ${suggestionsHtml}
            ${reportsHtml}

            <div class="wsa-card">
              <h3>Performance Trend</h3>
              <div class="wsa-chart-wrap">
                <canvas id="wsaChart"></canvas>
              </div>
            </div>

          </div>
        `);

        // chart
        if (recentReports.length > 0) {
          const ctx = document.getElementById("wsaChart");

          new Chart(ctx, {
            type: "line",
            data: {
              labels: recentReports.map((r) => r.time),
              datasets: [
                {
                  label: "Score",
                  data: recentReports.map((r) => r.data.score),
                  borderWidth: 2,
                  tension: 0.4,
                  fill: true,
                  pointRadius: 3,
                  backgroundColor: "rgba(59,130,246,0.1)",
                  borderColor: "#3b82f6",
                },
              ],
            },
            options: {
              responsive: true,
              maintainAspectRatio: false,

              plugins: {
                legend: {
                  display: false,
                },
              },

              scales: {
                x: {
                  ticks: {
                    maxTicksLimit: 5,
                  },
                },
                y: {
                  min: 0,
                  max: 100,
                  ticks: {
                    stepSize: 20,
                  },
                },
              },
            },
          });
        }
      })
      .catch(() => {
        $("#wsa-results").html("Error running scan");
      });
  });

  // csv
  $("#wsa-export-csv").on("click", function () {
    if (!lastReport) {
      alert("Run scan first!");
      return;
    }

    let csv = "Metric,Value\n";

    csv += `Score,${lastReport.score}\n`;
    csv += `LCP,${lastReport.lcp}\n`;
    csv += `CLS,${lastReport.cls}\n`;
    csv += `FID,${lastReport.fid}\n`;
    csv += `Speed Index,${lastReport.speed_index}\n`;

    lastReport.suggestions.forEach((s, i) => {
      csv += `Suggestion ${i + 1},${s}\n`;
    });

    let blob = new Blob([csv], { type: "text/csv" });
    let url = window.URL.createObjectURL(blob);

    let a = document.createElement("a");
    a.href = url;
    a.download = "speed-report.csv";
    a.click();
  });

  // pdf
  $("#wsa-export-pdf").on("click", function () {
    if (!lastReport) {
      alert("Run scan first!");
      return;
    }

    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();

    doc.setFontSize(16);
    doc.text("Speed Analyzer Report", 20, 20);

    doc.setFontSize(12);

    let y = 40;

    doc.text(`Score: ${lastReport.score}`, 20, y);
    y += 10;
    doc.text(`LCP: ${lastReport.lcp}`, 20, y);
    y += 10;
    doc.text(`CLS: ${lastReport.cls}`, 20, y);
    y += 10;
    doc.text(`FID: ${lastReport.fid}`, 20, y);
    y += 10;
    doc.text(`Speed Index: ${lastReport.speed_index}`, 20, y);
    y += 10;

    y += 10;
    doc.text("Suggestions:", 20, y);
    y += 10;

    (lastReport.suggestions || []).forEach((s) => {
      doc.text("- " + s, 20, y);
      y += 8;
    });

    doc.save("speed-report.pdf");
  });
});
