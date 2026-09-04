import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { motion } from 'framer-motion';
import { Wallet, Search, RefreshCw, Eye, CheckCircle, AlertCircle, Clock } from 'lucide-react';
import api from '../../api';

const StudentBilling = () => {
  const navigate = useNavigate();
  const [students, setStudents] = useState([]);
  const [stats, setStats] = useState({ expected: 0, collected: 0 });
  const [loading, setLoading] = useState(true);
  const [generating, setGenerating] = useState(false);
  const [searchTerm, setSearchTerm] = useState('');

  useEffect(() => {
    fetchBills();
  }, []);

  const fetchBills = async () => {
    try {
      const res = await api.get('/finance/bills');
      setStudents(Array.isArray(res.data?.students) ? res.data.students : []);
      setStats(res.data?.stats || { expected: 0, collected: 0 });
    } catch (err) {
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  const handleGenerateBills = async () => {
    if (!await window.confirmAction("This will generate or update tuition invoices for ALL active students based on their class fee structure. Continue?")) return;
    setGenerating(true);
    try {
      const res = await api.post('/finance/bills/generate');
      alert(`Successfully generated/updated ${res.data.generated} student bills.`);
      fetchBills();
    } catch (err) {
      alert('Failed to generate bills.');
    } finally {
      setGenerating(false);
    }
  };

  const filteredStudents = students.filter(s => 
    (s.first_name || '').toLowerCase().includes(searchTerm.toLowerCase()) || 
    (s.surname || '').toLowerCase().includes(searchTerm.toLowerCase()) ||
    (s.registration_number || '').toLowerCase().includes(searchTerm.toLowerCase()) ||
    (s.class_name || '').toLowerCase().includes(searchTerm.toLowerCase())
  );

  const formatMoney = (amount) => {
    return new Intl.NumberFormat('en-NG', { style: 'currency', currency: 'NGN' }).format(amount);
  };

  const getStatusBadge = (status) => {
    if (status === 'paid') return <span className="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-medium bg-green-50 text-green-600 border border-green-100"><CheckCircle className="w-3 h-3"/> Paid</span>;
    if (status === 'partial') return <span className="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-medium bg-orange-50 text-orange-600 border border-orange-100"><Clock className="w-3 h-3"/> Partial</span>;
    return <span className="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-medium bg-red-50 text-red-600 border border-red-100"><AlertCircle className="w-3 h-3"/> Unpaid</span>;
  };

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div className="flex items-center gap-3">
          <div className="w-10 h-10 rounded-xl bg-royal-blue/10 flex items-center justify-center text-royal-blue">
            <Wallet className="w-6 h-6" />
          </div>
          <div>
            <h1 className="text-2xl font-bold text-slate-800">Student Billing</h1>
            <p className="text-sm text-slate-500 mt-1">Manage session invoices and payments</p>
          </div>
        </div>
        <button 
          onClick={handleGenerateBills}
          disabled={generating}
          className="glass-button flex items-center gap-2"
        >
          <RefreshCw className={`w-4 h-4 ${generating ? 'animate-spin' : ''}`} /> 
          {generating ? 'Generating...' : 'Generate Session Bills'}
        </button>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div className="glass-card p-5 border-l-4 border-l-royal-blue">
          <p className="text-sm font-medium text-slate-500 mb-1">Expected Revenue</p>
          <h3 className="text-2xl font-bold text-slate-800">{formatMoney(stats.expected)}</h3>
        </div>
        <div className="glass-card p-5 border-l-4 border-l-green-500">
          <p className="text-sm font-medium text-slate-500 mb-1">Total Collected</p>
          <h3 className="text-2xl font-bold text-slate-800">{formatMoney(stats.collected)}</h3>
        </div>
        <div className="glass-card p-5 border-l-4 border-l-red-500">
          <p className="text-sm font-medium text-slate-500 mb-1">Outstanding Balance</p>
          <h3 className="text-2xl font-bold text-slate-800">{formatMoney(stats.expected - stats.collected)}</h3>
        </div>
      </div>

      <div className="glass-card overflow-hidden">
        <div className="p-4 border-b border-slate-200 bg-white/50 flex flex-col sm:flex-row justify-between items-center gap-4">
          <h2 className="font-semibold text-slate-700">Student Invoices</h2>
          <div className="relative w-full sm:w-64">
            <Search className="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
            <input 
              type="text" 
              placeholder="Search students..." 
              value={searchTerm}
              onChange={e => setSearchTerm(e.target.value)}
              className="w-full pl-9 pr-4 py-2 bg-white border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-royal-blue/20"
            />
          </div>
        </div>
        
        <div className="overflow-x-auto">
          <table className="w-full text-left text-sm text-slate-600 min-w-[800px]">
            <thead className="bg-slate-50/50 text-xs uppercase text-slate-500 font-semibold border-b border-slate-200">
              <tr>
                <th className="px-6 py-4">Student</th>
                <th className="px-6 py-4">Class</th>
                <th className="px-6 py-4">Total Billed</th>
                <th className="px-6 py-4">Amount Paid</th>
                <th className="px-6 py-4">Balance</th>
                <th className="px-6 py-4">Status</th>
                <th className="px-6 py-4 text-right">Actions</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100">
              {loading ? (
                <tr>
                  <td colSpan="7" className="px-6 py-8 text-center">Loading...</td>
                </tr>
              ) : filteredStudents.length === 0 ? (
                <tr>
                  <td colSpan="7" className="px-6 py-8 text-center text-slate-400">No student bills found. Click Generate to create them.</td>
                </tr>
              ) : (
                filteredStudents.map((st, idx) => {
                  const hasBill = st.fee_id != null;
                  const total = parseFloat(st.total_amount || 0);
                  const paid = parseFloat(st.paid_amount || 0);
                  const balance = total - paid;
                  
                  return (
                    <motion.tr 
                      initial={{ opacity: 0, y: 10 }}
                      animate={{ opacity: 1, y: 0 }}
                      transition={{ delay: idx * 0.02 }}
                      key={st.student_id} 
                      className="hover:bg-slate-50/50"
                    >
                      <td className="px-6 py-4">
                        <p className="font-semibold text-slate-800">{st.first_name} {st.surname}</p>
                        <p className="text-xs text-slate-500 font-mono mt-0.5">{st.registration_number}</p>
                      </td>
                      <td className="px-6 py-4">{st.class_name}</td>
                      <td className="px-6 py-4 font-mono">{hasBill ? formatMoney(total) : '-'}</td>
                      <td className="px-6 py-4 font-mono text-green-600">{hasBill ? formatMoney(paid) : '-'}</td>
                      <td className="px-6 py-4 font-mono text-red-500">{hasBill ? formatMoney(balance) : '-'}</td>
                      <td className="px-6 py-4">
                        {hasBill ? getStatusBadge(st.status) : <span className="text-xs text-slate-400 italic">No Bill</span>}
                      </td>
                      <td className="px-6 py-4 text-right">
                        <button 
                          onClick={() => navigate(`/finance/student/${st.student_id}`)}
                          disabled={!hasBill}
                          className={`p-2 rounded-lg transition-colors ${hasBill ? 'text-royal-blue bg-royal-blue/10 hover:bg-royal-blue/20' : 'text-slate-300 bg-slate-50 cursor-not-allowed'}`}
                        >
                          <Eye className="w-4 h-4" />
                        </button>
                      </td>
                    </motion.tr>
                  );
                })
              )}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
};

export default StudentBilling;
