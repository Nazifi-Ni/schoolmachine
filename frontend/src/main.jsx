import React from 'react'
import ReactDOM from 'react-dom/client'
import App from './App.jsx'
import './index.css'
import toast from 'react-hot-toast'

import Swal from 'sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';

// Override default window.alert with a beautiful toast!
window.alert = (message) => {
  const msgLower = (message || '').toString().toLowerCase();
  if (msgLower.includes('fail') || msgLower.includes('error') || msgLower.includes('invalid') || msgLower.includes('wrong')) {
    toast.error(message);
  } else if (msgLower.includes('success') || msgLower.includes('approved') || msgLower.includes('done')) {
    toast.success(message);
  } else {
    toast(message, {
      icon: '🔔',
    });
  }
};

// Premium, expensive UI/UX Confirmation Dialog using SweetAlert2
window.confirmAction = async (message) => {
  const result = await Swal.fire({
    title: 'Are you sure?',
    text: message,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#0955ac',
    cancelButtonColor: '#ef4444',
    confirmButtonText: 'Yes, proceed',
    cancelButtonText: 'Cancel',
    background: 'rgba(255, 255, 255, 0.95)',
    backdrop: `rgba(15, 23, 42, 0.4) backdrop-blur-sm`,
    customClass: {
      popup: 'rounded-3xl shadow-[0_20px_60px_rgba(0,0,0,0.2)] border border-slate-200/50 backdrop-blur-xl',
      title: 'text-2xl font-bold text-slate-800 font-sans tracking-tight',
      htmlContainer: 'text-slate-500 font-medium',
      confirmButton: 'rounded-xl font-bold px-8 py-3 shadow-[0_4px_12px_rgba(9,85,172,0.2)] hover:shadow-[0_6px_16px_rgba(9,85,172,0.3)] transition-all',
      cancelButton: 'rounded-xl font-bold px-8 py-3 shadow-[0_4px_12px_rgba(239,68,68,0.2)] hover:shadow-[0_6px_16px_rgba(239,68,68,0.3)] transition-all'
    }
  });
  return result.isConfirmed;
};

ReactDOM.createRoot(document.getElementById('root')).render(
  <React.StrictMode>
    <App />
  </React.StrictMode>,
)
