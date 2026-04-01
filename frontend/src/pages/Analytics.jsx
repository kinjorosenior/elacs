import React, { useEffect, useState } from "react";
import AdminLayout from "../layouts/AdminLayout";
import Charts from "../components/Charts";
import DeviceTable from "../components/DeviceTable";
import DashboardCard from "../components/DashboardCard";
import { getAnalytics } from "../services/api";
import axios from "axios";

function Analytics() {
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

  if (loading) return <AdminLayout>Loading analytics...</AdminLayout>;

  return (
    <AdminLayout>
      <h1>Analytics & Reports</h1>

      <div style={{ display: "grid", gridTemplateColumns: "repeat(auto-fit, minmax(200px, 1fr))", gap: "20px", marginTop: "20px" }}>
        <DashboardCard title="Total Students" value={data.total_students?.total || 0} />
        <DashboardCard title="Total Devices" value={data.total_devices?.total || 0} />
        <DashboardCard title="Devices Inside" value={data.devices_inside?.total || 0} />
        <DashboardCard title="Today's Check-ins" value={data.today_checkins?.total || 0} />
      </div>

      <Charts data={data} stats={stats} style={{ marginTop: "30px" }} />

      <DeviceTable devices={devicesInside} title="Devices Currently Inside (from checkins)" />
      <DeviceTable devices={devicesOutside} title="Checked Out Devices" />
    </AdminLayout>
  );
}

export default Analytics;

