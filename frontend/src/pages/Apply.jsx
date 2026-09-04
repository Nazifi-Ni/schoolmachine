import React, { useState, useEffect } from 'react';
import { motion } from 'framer-motion';
import { School, CheckCircle, ArrowRight, User, BookOpen, MapPin, Phone } from 'lucide-react';
import api from '../api';
import { Link } from 'react-router-dom';

const Apply = () => {
  const [classes, setClasses] = useState([]);
  const [submitted, setSubmitted] = useState(false);
  const [loading, setLoading] = useState(false);
  const [formData, setFormData] = useState({
    first_name: '',
    surname: '',
    middle_name: '',
    gender: 'Male',
    date_of_birth: '',
    desired_class_id: '',
    guardian_name: '',
    guardian_phone: '',
    guardian_email: '',
    address: ''
  });

  useEffect(() => {
    api.get('/public/classes')
       .then(res => setClasses(Array.isArray(res.data) ? res.data : []))
       .catch(err => console.error(err));
  }, []);

  const handleChange = (e) => {
    setFormData({ ...formData, [e.target.name]: e.target.value });
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    try {
      await api.post('/admissions/apply', formData);
      setSubmitted(true);
    } catch (err) {
      alert(err.response?.data?.error || 'Failed to submit application.');
    } finally {
      setLoading(false);
    }
  };

  if (submitted) {
    return (
      <div className="min-h-screen bg-slate-50 flex flex-col items-center justify-center p-6">
        <motion.div 
          initial={{ scale: 0.9, opacity: 0 }}
          animate={{ scale: 1, opacity: 1 }}
          className="max-w-md w-full bg-white rounded-3xl shadow-xl p-8 text-center space-y-6"
        >
          <div className="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto">
            <CheckCircle className="w-10 h-10 text-green-500" />
          </div>
          <h1 className="text-3xl font-bold text-slate-800">Application Received!</h1>
          <p className="text-slate-600">
            Thank you for applying to Ismail Ahmad Memorial Academy. We have received your application for <strong className="text-slate-800 uppercase">{formData.first_name}</strong>. 
            Our admissions team will review it and contact you shortly. 
          </p>
          <div className="bg-slate-50 p-4 rounded-xl border border-slate-100">
            <p className="text-sm text-slate-600">For immediate assistance or inquiries regarding your application, please reach out to us on WhatsApp:</p>
            <p className="text-lg font-bold text-royal-blue mt-2">+2349018269353</p>
          </div>
          <Link to="/" className="inline-block mt-4 px-6 py-2.5 bg-royal-blue text-white rounded-md font-bold hover:bg-blue-800 transition-colors">
            Return to Homepage
          </Link>
        </motion.div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-slate-50 py-12 px-4 sm:px-6">
      <div className="max-w-3xl mx-auto">
        <div className="text-center mb-10">
          <div className="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-white shadow-sm border border-slate-100 mb-4">
             <img src="/assets/images/school_logo.jpg" alt="Logo" className="w-12 h-12 object-contain" onError={(e) => e.target.src='https://via.placeholder.com/48'} />
          </div>
          <h1 className="text-4xl font-extrabold text-slate-900 tracking-tight">Online Admission</h1>
          <p className="text-lg text-slate-500 mt-2">Apply for the upcoming academic session at IAMS ARMS</p>
        </div>

        <motion.div 
          initial={{ y: 20, opacity: 0 }}
          animate={{ y: 0, opacity: 1 }}
          className="bg-white/80 backdrop-blur-xl border border-white shadow-2xl rounded-3xl overflow-hidden"
        >
          <div className="p-6 sm:p-10">
            <form onSubmit={handleSubmit} className="space-y-8">
              
              {/* Student Details */}
              <section>
                <h3 className="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2 border-b border-slate-100 pb-2">
                  <User className="w-5 h-5 text-royal-blue" />
                  Student Information
                </h3>
                <div className="grid grid-cols-1 sm:grid-cols-2 gap-5">
                  <div>
                    <label className="block text-sm font-medium text-slate-700 mb-1">First Name *</label>
                    <input type="text" name="first_name" required value={formData.first_name} onChange={handleChange} className="w-full bg-slate-50 border border-slate-200 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-royal-blue/20 outline-none transition-all" />
                  </div>
                  <div>
                    <label className="block text-sm font-medium text-slate-700 mb-1">Surname *</label>
                    <input type="text" name="surname" required value={formData.surname} onChange={handleChange} className="w-full bg-slate-50 border border-slate-200 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-royal-blue/20 outline-none transition-all" />
                  </div>
                  <div>
                    <label className="block text-sm font-medium text-slate-700 mb-1">Middle Name</label>
                    <input type="text" name="middle_name" value={formData.middle_name} onChange={handleChange} className="w-full bg-slate-50 border border-slate-200 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-royal-blue/20 outline-none transition-all" />
                  </div>
                  <div>
                    <label className="block text-sm font-medium text-slate-700 mb-1">Gender *</label>
                    <select name="gender" required value={formData.gender} onChange={handleChange} className="w-full bg-slate-50 border border-slate-200 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-royal-blue/20 outline-none transition-all">
                      <option value="Male">Male</option>
                      <option value="Female">Female</option>
                    </select>
                  </div>
                  <div>
                    <label className="block text-sm font-medium text-slate-700 mb-1">Date of Birth</label>
                    <input type="date" name="date_of_birth" value={formData.date_of_birth} onChange={handleChange} className="w-full bg-slate-50 border border-slate-200 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-royal-blue/20 outline-none transition-all" />
                  </div>
                </div>
              </section>

              {/* Class Selection */}
              <section>
                <h3 className="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2 border-b border-slate-100 pb-2">
                  <BookOpen className="w-5 h-5 text-royal-blue" />
                  Academic Details
                </h3>
                <div>
                  <label className="block text-sm font-medium text-slate-700 mb-1">Desired Class *</label>
                  <select name="desired_class_id" required value={formData.desired_class_id} onChange={handleChange} className="w-full bg-slate-50 border border-slate-200 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-royal-blue/20 outline-none transition-all">
                    <option value="">-- Select Class --</option>
                    {classes.map(c => (
                      <option key={c.id} value={c.id}>{c.name} ({c.level})</option>
                    ))}
                  </select>
                </div>
              </section>

              {/* Guardian Details */}
              <section>
                <h3 className="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2 border-b border-slate-100 pb-2">
                  <Phone className="w-5 h-5 text-royal-blue" />
                  Parent / Guardian Details
                </h3>
                <div className="grid grid-cols-1 sm:grid-cols-2 gap-5">
                  <div className="sm:col-span-2">
                    <label className="block text-sm font-medium text-slate-700 mb-1">Full Name *</label>
                    <input type="text" name="guardian_name" required value={formData.guardian_name} onChange={handleChange} className="w-full bg-slate-50 border border-slate-200 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-royal-blue/20 outline-none transition-all" />
                  </div>
                  <div>
                    <label className="block text-sm font-medium text-slate-700 mb-1">Phone Number *</label>
                    <input type="tel" name="guardian_phone" required value={formData.guardian_phone} onChange={handleChange} className="w-full bg-slate-50 border border-slate-200 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-royal-blue/20 outline-none transition-all" />
                  </div>
                  <div>
                    <label className="block text-sm font-medium text-slate-700 mb-1">Email Address</label>
                    <input type="email" name="guardian_email" value={formData.guardian_email} onChange={handleChange} className="w-full bg-slate-50 border border-slate-200 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-royal-blue/20 outline-none transition-all" />
                  </div>
                  <div className="sm:col-span-2">
                    <label className="block text-sm font-medium text-slate-700 mb-1 flex items-center gap-1">
                      <MapPin className="w-4 h-4 text-slate-400" /> Home Address
                    </label>
                    <textarea name="address" rows="2" value={formData.address} onChange={handleChange} className="w-full bg-slate-50 border border-slate-200 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-royal-blue/20 outline-none transition-all"></textarea>
                  </div>
                </div>
              </section>

              <div className="pt-6 border-t border-slate-200">
                <button 
                  type="submit" 
                  disabled={loading}
                  className="w-full flex items-center justify-center gap-2 bg-royal-blue text-white font-bold text-lg py-4 rounded-xl hover:bg-blue-700 hover:shadow-lg hover:shadow-royal-blue/30 transition-all active:scale-[0.98]"
                >
                  {loading ? 'Submitting...' : 'Submit Application'} <ArrowRight className="w-5 h-5" />
                </button>
              </div>

            </form>
          </div>
        </motion.div>
      </div>
    </div>
  );
};

export default Apply;
