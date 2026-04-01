import React from "react";
import { Pie, Bar } from "react-chartjs-2";
import "chart.js/auto";

function Charts({ data, stats }) {
  const pieData = {
    labels: ["Check-ins", "Check-outs"],
    datasets: [
      {
        data: [stats.total_checkins, stats.total_checkouts],
        backgroundColor: ["#3b82f6", "#f97316"]
      }
    ]
  };

  const trustPieData = {
    labels: data.trust_distribution?.map(d => d.category) || [],
    datasets: [
      {
        data: data.trust_distribution?.map(d => d.total) || [],
        backgroundColor: ["#4ade80", "#facc15", "#f87171"]
      }
    ]
  };

  return (
    <div style={{ display: "flex", gap: "30px", flexWrap: "wrap", marginTop: "30px" }}>
      <div style={{ width: "300px" }}>
        <h4>Check-ins vs Check-outs</h4>
        <Pie data={pieData} />
      </div>
      <div style={{ width: "300px" }}>
        <h4>Student Trust Distribution</h4>
        <Pie data={trustPieData} />
      </div>
      <div style={{ width: "300px" }}>
        <h4>Check-ins vs Check-outs (Bar)</h4>
        <Bar
          data={{
            labels: ["Check-ins", "Check-outs"],
            datasets: [
              {
                label: "Activity",
                data: [stats.total_checkins, stats.total_checkouts],
                backgroundColor: ["#3b82f6", "#f97316"]
              }
            ]
          }}
        />
      </div>
    </div>
  );
}

export default Charts;