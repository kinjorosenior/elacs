import React from 'react';
import ReactDOM from 'react-dom/client';
import App from './App.jsx';
import './App.css';

// 🔹 Stable: do not use StrictMode in dev to avoid double-mount flashing
ReactDOM.createRoot(document.getElementById('root')).render(
  <App />
);