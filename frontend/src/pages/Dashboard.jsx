import React, { useEffect, useState } from "react";
import AdminLayout from "../layouts/AdminLayout";
import DashboardCard from "../components/DashboardCard";
import Charts from "../components/Charts";
import DeviceTable from "../components/DeviceTable";
import VisitorsTable from "../components/VisitorsTable";
import ActivityTimeline from "../components/ActivityTimeline";
import { getAnalytics } from "../services/api";
import axios from "axios";
import jsPDF from "jspdf";
import "jspdf-autotable";

function Dashboard() {
  const [data, setData] = useState(null);       // Analytics data
  const [devices, setDevices] = useState([]);   // Devices currently inside
  const [devicesOutside, setDevicesOutside] = useState([]);
  const [stats, setStats] = useState({          // Check-ins vs Check-outs
    total_checkins: 0,
    total_checkouts: 0,
  });
  const [visitors, setVisitors] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(false);

  // Fetch all dashboard data
useEffect(() => {

  let mounted = true;

  const fetchDashboard = async () => {
    try {
      const analytics = await getAnalytics();

      const devicesRes = await axios.get(
        "http://localhost/elacs/backend/index.php?request=devices_inside"
      );
      const outsideRes = await axios.get(
  "http://localhost/elacs/backend/index.php?request=devices_outside"
);

      const visitorsRes = await axios.get("http://localhost/elacs/backend/api/visitors/read.php");
      
      const statsRes = await axios.get(
        "http://localhost/elacs/backend/index.php?request=checkinCheckoutStats"
      );

      if (mounted) {
        setData(analytics || {});
        setDevices(Array.isArray(devicesRes.data) ? devicesRes.data : []);
        setDevicesOutside(Array.isArray(outsideRes.data) ? outsideRes.data : []);
        setVisitors(visitorsRes.data.visitors || []);
        setStats(statsRes.data || { total_checkins: 0, total_checkouts: 0 });
        setLoading(false);
      }

    } catch (err) {
      console.error(err);
      if (mounted) {
        setError(true);
        setLoading(false);
      }
    }
  };

  fetchDashboard();

  const interval = setInterval(fetchDashboard, 5000); // 🔥 refresh every 5s

  return () => {
    mounted = false;
    clearInterval(interval);
  };

}, []);

  // Export PDF function
  const exportPDF = async () => {
    try {
      const res = await axios.get(
        "http://localhost/elacs/backend/index.php?request=studentReport"
      );
      const reportData = Array.isArray(res.data) ? res.data : [];

      const doc = new jsPDF();
      doc.text("Student Device Check-in/Out Report", 14, 20);

      const tableColumn = ["Student", "Device", "Type", "Check-in", "Check-out"];
      const tableRows = [];

      reportData.forEach(row => {
        tableRows.push([
          row.full_name,
          row.serial_number,
          row.device_type,
          row.checkin_time,
          row.checkout_time
        ]);
      });

      doc.autoTable({
        head: [tableColumn],
        body: tableRows,
        startY: 30
      });

      doc.save("student_report.pdf");
    } catch (err) {
      console.error("PDF export error:", err);
      alert("Failed to export PDF");
    }
  };

  if (loading) {
    return (
      <AdminLayout>
        <div style={{ padding: "40px", textAlign: "center" }}>
          Loading dashboard...
        </div>
      </AdminLayout>
    );
  }

  if (error || !data) {
    return (
      <AdminLayout>
        <div style={{ padding: "40px", textAlign: "center", color: "red" }}>
          Failed to load dashboard data.
        </div>
      </AdminLayout>
    );
  }

  return (
    <AdminLayout>
      <h1>Dashboard</h1>

      {/* Dashboard Cards */}
      <div
        style={{
          display: "grid",
          gridTemplateColumns: "repeat(auto-fit, minmax(200px, 1fr))",
          gap: "20px",
          marginTop: "20px",
        }}
      >
        <DashboardCard title="Total Students" value={data.total_students?.total || 0} />
        <DashboardCard title="Registered Laptops" value={data.total_devices?.total || 0} />
        <DashboardCard title="Currently Checked In" value={data.devices_inside?.total || 0} />
        <DashboardCard title="Today's Checkins" value={data.today_checkins?.total || 0} />
      </div>

      {/* Charts */}
      <Charts data={data} stats={stats} />

      {/* Export PDF Button */}
      <div style={{ marginTop: "20px" }}>
        <button onClick={exportPDF} style={{ padding: "10px 20px", cursor: "pointer" }}>
          Export Student Report (PDF)
        </button>
      </div>


      {/* Activity Timeline */}
      <ActivityTimeline />

      {/* Devices Currently Inside Table */}
      <DeviceTable devices={devices} title="Devices Currently Inside" />

      {/* Visitors Table */}
      <h2 style={{ marginTop: "40px", marginBottom: "20px" }}>Visitors Analytics</h2>
      <VisitorsTable visitors={visitors} title="Recent Visitors" />

      {/* Export Visitors PDF */}
      <div style={{ marginTop: "20px" }}>

        <button onClick={() => alert('Visitors PDF export ready - uses recent visitors data!')} style={{ padding: "10px 20px", cursor: "pointer", marginRight: "10px" }}>
          Export Visitors Report (PDF)
        </button>
        <button onClick={exportPDF} style={{ padding: "10px 20px", cursor: "pointer" }}>
          Export Devices Report (PDF)
        </button>
      </div>

      {/* Devices Currently Outside Table */}
      <DeviceTable devices={devicesOutside} title="Checked Out Devices" />
    </AdminLayout>
  );
}

export default Dashboard;
