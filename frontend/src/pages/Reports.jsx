import React, { useEffect, useState } from "react";
import AdminLayout from "../layouts/AdminLayout";
import Charts from "../components/Charts";
import DeviceTable from "../components/DeviceTable";
import DashboardCard from "../components/DashboardCard";
import { getAnalytics } from "../services/api";
import axios from "axios";
import jsPDF from "jspdf";
import "jspdf-autotable";

function Reports() {
  const [data, setData] = useState(null);
  const [devicesInside, setDevicesInside] = useState([]);
  const [devicesOutside, setDevicesOutside] = useState([]);
  const [stats, setStats] = useState({ total_checkins: 0, total_checkouts: 0 });
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetchData = async () => {
      try {
        const [analyticsRes, insideRes, outsideRes, statsRes] = await Promise.all([
          getAnalytics(),
          axios.get("http://localhost/elacs/backend/index.php?request=devices_inside"),
          axios.get("http://localhost/elacs/backend/index.php?request=devices_outside"),
          axios.get("http://localhost/elacs/backend/index.php?request=checkinCheckoutStats")
        ]);

        setData(analyticsRes || {});
        setDevicesInside(Array.isArray(insideRes.data) ? insideRes.data : []);
        setDevicesOutside(Array.isArray(outsideRes.data) ? outsideRes.data : []);
        setStats(statsRes.data || { total_checkins: 0, total_checkouts: 0 });
        setLoading(false);
      } catch (err) {
        console.error(err);
        setLoading(false);
      }
    };

    fetchData();
  }, []);

  const exportPDF = async () => {
    try {
      const res = await axios.get("http://localhost/elacs/backend/index.php?request=studentReport");
      const reportData = Array.isArray(res.data) ? res.data : [];

      const doc = new jsPDF();
      doc.text("Library Device Usage Report", 14, 20);
      const tableColumn = ["Student", "Device", "Type", "Check-in", "Check-out"];
      const tableRows = reportData.map(row => [
        row.full_name,
        row.serial_number,
        row.device_type,
        row.checkin_time,
        row.checkout_time
      ]);

      doc.autoTable({ head: [tableColumn], body: tableRows, startY: 30 });
      doc.save("library_report.pdf");
    } catch (err) {
      alert("Failed to export PDF");
    }
  };

  if (loading) return <AdminLayout>Loading reports...</AdminLayout>;

  return (
    <AdminLayout>
      <h1>Reports & Analytics</h1>
      
      <div style={{ marginBottom: "20px" }}>
        <button onClick={exportPDF} style={{ padding: "10px 20px", background: "#3b82f6", color: "white", border: "none", borderRadius: "5px" }}>
          📄 Export Full Report (PDF)
        </button>
      </div>

      <div style={{ display: "grid", gridTemplateColumns: "repeat(auto-fit, minmax(200px, 1fr))", gap: "20px", marginTop: "20px" }}>
        <DashboardCard title="Total Check-ins" value={stats.total_checkins || 0} />
        <DashboardCard title="Total Check-outs" value={stats.total_checkouts || 0} />
        <DashboardCard title="Devices Currently In" value={devicesInside.length} />
        <DashboardCard title="Devices Checked Out" value={devicesOutside.length} />
      </div>

      <Charts data={data} stats={stats} />

      <DeviceTable devices={devicesInside} title="Active Check-ins (Inside Library)" />
      <DeviceTable devices={devicesOutside} title="Checked Out Devices" />
    </AdminLayout>
  );
}

export default Reports;

