import axios from "axios";

const API = axios.create({
  baseURL: "http://localhost/elacs/backend/"
});

export const getAnalytics = async () => {
  const response = await API.get("index.php?request=analytics");
  return response.data;
};

export const getDevicesInside = () => API.get("index.php?request=devices_inside").then(r => r.data);
export const getDevicesOutside = () => API.get("index.php?request=devices_outside").then(r => r.data);
export const getCheckinStats = () => API.get("index.php?request=checkinCheckoutStats").then(r => r.data);

