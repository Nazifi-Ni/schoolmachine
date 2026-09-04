import React, { useState } from 'react';
import api from '../api';
import { KeyRound, ShieldCheck, AlertCircle, Eye, EyeOff, Lock } from 'lucide-react';
import { motion } from 'framer-motion';

const ChangePassword = () => {
  const [formData, setFormData] = useState({
    current_password: '',
    new_password: '',
    confirm_password: ''
  });
  const [loading, setLoading] = useState(false);
  const [showCurrent, setShowCurrent] = useState(false);
  const [showNew, setShowNew] = useState(false);

  const getStrength = (pass) => {
    let s = 0;
    if (pass.length > 5) s += 1;
    if (pass.length >= 8) s += 1;
    if (/[A-Z]/.test(pass)) s += 1;
    if (/[0-9]/.test(pass)) s += 1;
    if (/[^A-Za-z0-9]/.test(pass)) s += 1;
    return s;
  };

  const strength = getStrength(formData.new_password);
  
  const getStrengthColor = () => {
    if (strength === 0) return 'bg-slate-200';
    if (strength <= 2) return 'bg-red-500';
    if (strength <= 3) return 'bg-yellow-500';
    return 'bg-green-500';
  };

  const getStrengthText = () => {
    if (formData.new_password.length === 0) return 'Enter a password';
    if (strength <= 2) return 'Weak';
    if (strength <= 3) return 'Good';
    return 'Strong';
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    if (formData.new_password !== formData.confirm_password) {
      window.alert('Error: New passwords do not match.');
      return;
    }
    if (strength < 2) {
      window.alert('Error: Password is too weak. Please use a stronger password.');
      return;
    }
    
    setLoading(true);

    try {
      const response = await api.post('/change-password', {
        current_password: formData.current_password,
        new_password: formData.new_password
      });
      if (response.data.success) {
        window.alert('Success: Password changed successfully.');
        setFormData({ current_password: '', new_password: '', confirm_password: '' });
      }
    } catch (err) {
      window.alert('Error: ' + (err.response?.data?.error || 'Failed to change password.'));
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="max-w-xl mx-auto space-y-6">
      <div className="flex justify-between items-end mb-8">
        <div>
          <h1 className="text-3xl font-bold text-slate-800 tracking-tight">Change Password</h1>
          <p className="text-slate-500 mt-1">Update your account security credentials securely.</p>
        </div>
      </div>

      <motion.div 
        initial={{ opacity: 0, y: 10 }}
        animate={{ opacity: 1, y: 0 }}
        className="glass-card overflow-hidden border border-slate-200/60"
      >
        <div className="p-6 border-b border-slate-200/50 bg-brand-50/30 flex items-center gap-4">
          <div className="w-12 h-12 rounded-2xl bg-brand-100 flex items-center justify-center text-brand-600 shadow-inner">
            <KeyRound className="w-6 h-6" />
          </div>
          <div>
            <h3 className="font-semibold text-slate-800 text-lg">Security Details</h3>
            <p className="text-sm text-slate-500 leading-snug">Ensure your account is using a long, random password to stay secure.</p>
          </div>
        </div>

        <form onSubmit={handleSubmit} className="p-6 space-y-6">
          <div className="space-y-5">
            {/* Current Password */}
            <div>
              <label className="block text-sm font-semibold text-slate-700 mb-1.5">Current Password</label>
              <div className="relative">
                <div className="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                  <Lock className="w-4 h-4" />
                </div>
                <input 
                  type={showCurrent ? "text" : "password"}
                  required
                  placeholder="Enter your current password"
                  className="w-full pl-10 pr-12 py-2.5 glass-input font-medium"
                  value={formData.current_password}
                  onChange={e => setFormData({...formData, current_password: e.target.value})}
                />
                <button 
                  type="button" 
                  onClick={() => setShowCurrent(!showCurrent)}
                  className="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-brand-600 transition-colors"
                >
                  {showCurrent ? <EyeOff className="w-4 h-4" /> : <Eye className="w-4 h-4" />}
                </button>
              </div>
            </div>
            
            <div className="pt-2 border-t border-slate-100/50"></div>

            {/* New Password */}
            <div>
              <label className="block text-sm font-semibold text-slate-700 mb-1.5">New Password</label>
              <div className="relative">
                <div className="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                  <ShieldCheck className="w-4 h-4" />
                </div>
                <input 
                  type={showNew ? "text" : "password"}
                  required
                  placeholder="Create a new strong password"
                  className="w-full pl-10 pr-12 py-2.5 glass-input font-medium"
                  value={formData.new_password}
                  onChange={e => setFormData({...formData, new_password: e.target.value})}
                />
                <button 
                  type="button" 
                  onClick={() => setShowNew(!showNew)}
                  className="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-brand-600 transition-colors"
                >
                  {showNew ? <EyeOff className="w-4 h-4" /> : <Eye className="w-4 h-4" />}
                </button>
              </div>
              
              {/* Password Strength Meter */}
              <div className="mt-3">
                <div className="flex justify-between items-center mb-1.5">
                  <span className="text-xs font-semibold text-slate-500 uppercase tracking-wider">Password Strength</span>
                  <span className={`text-xs font-bold ${strength <= 2 ? 'text-red-500' : strength <= 3 ? 'text-yellow-600' : 'text-green-600'}`}>
                    {getStrengthText()}
                  </span>
                </div>
                <div className="flex gap-1.5 h-1.5 w-full">
                  {[1, 2, 3, 4].map(level => (
                    <div 
                      key={level} 
                      className={`flex-1 rounded-full transition-all duration-300 ${strength >= level ? getStrengthColor() : 'bg-slate-200'}`}
                    ></div>
                  ))}
                </div>
              </div>
            </div>
            
            {/* Confirm Password */}
            <div>
              <label className="block text-sm font-semibold text-slate-700 mb-1.5">Confirm New Password</label>
              <div className="relative">
                <div className="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                  <Lock className="w-4 h-4" />
                </div>
                <input 
                  type={showNew ? "text" : "password"}
                  required
                  placeholder="Repeat your new password"
                  className={`w-full pl-10 pr-4 py-2.5 glass-input font-medium ${
                    formData.confirm_password && formData.new_password !== formData.confirm_password 
                    ? 'border-red-300 focus:ring-red-200 bg-red-50/50' 
                    : ''
                  }`}
                  value={formData.confirm_password}
                  onChange={e => setFormData({...formData, confirm_password: e.target.value})}
                />
              </div>
              {formData.confirm_password && formData.new_password !== formData.confirm_password && (
                <p className="text-xs text-red-500 mt-1.5 font-medium flex items-center gap-1">
                  <AlertCircle className="w-3.5 h-3.5" /> Passwords do not match
                </p>
              )}
            </div>
          </div>

          <div className="pt-6 flex justify-end">
            <button 
              type="submit"
              disabled={loading || (formData.new_password && formData.new_password !== formData.confirm_password)}
              className="px-8 py-3 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-xl shadow-[0_4px_12px_rgba(9,85,172,0.2)] hover:shadow-[0_6px_16px_rgba(9,85,172,0.3)] transition-all disabled:opacity-50 disabled:hover:shadow-[0_4px_12px_rgba(9,85,172,0.2)] disabled:cursor-not-allowed flex items-center gap-2"
            >
              {loading ? (
                <>
                  <svg className="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                    <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  Updating...
                </>
              ) : (
                'Update Password'
              )}
            </button>
          </div>
        </form>
      </motion.div>
    </div>
  );
};

export default ChangePassword;
