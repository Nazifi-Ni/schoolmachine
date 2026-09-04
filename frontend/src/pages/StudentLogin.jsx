import React, { useState, useEffect } from 'react';
import { motion } from 'framer-motion';
import { LogIn, Key, User } from 'lucide-react';
import api from '../api';
import { useNavigate } from 'react-router-dom';

const StudentLogin = () => {
  const [formData, setFormData] = useState({ registration_number: '', pin: '' });
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);
  const navigate = useNavigate();

  useEffect(() => {
    const savedReg = localStorage.getItem('lastStudentReg');
    if (savedReg) {
      setFormData(prev => ({ ...prev, registration_number: savedReg }));
    }
  }, []);

  const handleLogin = async (e) => {
    e.preventDefault();
    setError('');
    setLoading(true);

    try {
      const response = await api.post('/student-login', formData);
      if (response.data.success) {
        localStorage.setItem('lastStudentReg', formData.registration_number);
        navigate('/student-portal');
      }
    } catch (err) {
      window.alert('Error: ' + (err.response?.data?.error || 'Login failed. Please try again.'));
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="min-h-screen flex items-center justify-center bg-gradient-to-br from-slate-100 to-slate-200 p-4">
      {/* Decorative background shapes */}
      <div className="absolute top-0 left-0 w-full h-full overflow-hidden -z-10 pointer-events-none">
        <div className="absolute -top-40 -left-40 w-96 h-96 bg-royal-blue/20 rounded-full blur-3xl"></div>
        <div className="absolute bottom-0 right-0 w-[500px] h-[500px] bg-blue-300/20 rounded-full blur-3xl"></div>
      </div>

      <motion.div 
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ duration: 0.5 }}
        className="glass-card w-full max-w-md p-8"
      >
        <div className="text-center mb-8">
          <div className="mx-auto w-20 h-20 bg-white rounded-full flex items-center justify-center shadow-sm mb-4 border border-slate-100 p-2">
            <img src="/assets/images/school_logo.jpg" alt="Logo" className="w-full h-full object-contain rounded-full" />
          </div>
          <h1 className="text-2xl font-bold text-slate-800">Student Portal</h1>
          <p className="text-sm text-slate-500 mt-1">Welcome back! Please login to your account.</p>
        </div>

        <form onSubmit={handleLogin} className="space-y-5">
          <div>
            <label htmlFor="regNumber" className="block text-sm font-semibold text-slate-700 mb-1.5">Registration Number</label>
            <div className="relative">
              <div className="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                <User className="h-5 w-5 text-slate-400" />
              </div>
              <input 
                id="regNumber"
                name="username"
                autoComplete="username"
                type="text" 
                required
                value={formData.registration_number}
                onChange={e => setFormData({...formData, registration_number: e.target.value})}
                className="w-full pl-10 pr-4 py-3 glass-input font-medium"
                placeholder="e.g. IAMS/2026/0123"
              />
            </div>
          </div>

          <div>
            <label htmlFor="pin" className="block text-sm font-semibold text-slate-700 mb-1.5">4-Digit PIN</label>
            <div className="relative">
              <div className="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                <Key className="h-5 w-5 text-slate-400" />
              </div>
              <input 
                id="pin"
                name="password"
                autoComplete="current-password"
                type="password" 
                required
                maxLength="4"
                value={formData.pin}
                onChange={e => setFormData({...formData, pin: e.target.value})}
                className="w-full pl-10 pr-4 py-3 glass-input font-medium tracking-[0.5em]"
                placeholder="••••"
              />
            </div>
            <p className="text-[11px] text-slate-500 mt-2 ml-1">
              Your PIN is the 4-digit code provided during admission.
            </p>
          </div>

          <div className="pt-4">
            <button
              type="submit"
              disabled={loading}
              className="w-full py-3.5 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-xl shadow-[0_4px_12px_rgba(9,85,172,0.2)] hover:shadow-[0_6px_16px_rgba(9,85,172,0.3)] transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
            >
              {loading ? (
                <>
                  <svg className="animate-spin -ml-1 mr-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                    <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  Accessing Portal...
                </>
              ) : (
                <>
                  <span className="text-[15px]">Login to Portal</span>
                  <LogIn className="h-4 w-4" />
                </>
              )}
            </button>
          </div>
        </form>

        <div className="mt-8 text-center">
          <p className="text-xs font-medium text-slate-400">
            Student Secure Gateway &copy; {new Date().getFullYear()}
          </p>
        </div>
      </motion.div>
    </div>
  );
};

export default StudentLogin;
