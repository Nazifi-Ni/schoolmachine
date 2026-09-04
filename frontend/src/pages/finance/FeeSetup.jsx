import React, { useState, useEffect } from 'react';
import { motion } from 'framer-motion';
import { Settings, Save, AlertCircle } from 'lucide-react';
import api from '../../api';

const FeeSetup = () => {
  const [classes, setClasses] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetchFees();
  }, []);

  const fetchFees = async () => {
    try {
      const res = await api.get('/finance/fees');
      setClasses(Array.isArray(res.data) ? res.data : []);
    } catch (err) {
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  const handleAmountChange = (index, value) => {
    const updated = [...classes];
    updated[index].amount = value;
    setClasses(updated);
  };

  const handleSave = async (classId, amount) => {
    try {
      await api.post('/finance/fees', { class_id: classId, amount: parseFloat(amount) });
      alert('Fee structure saved!');
    } catch (err) {
      alert('Failed to save fee structure');
    }
  };

  return (
    <div className="max-w-4xl mx-auto space-y-6">
      <div className="flex items-center gap-3 mb-6">
        <div className="w-10 h-10 rounded-xl bg-royal-blue/10 flex items-center justify-center text-royal-blue">
          <Settings className="w-6 h-6" />
        </div>
        <div>
          <h1 className="text-2xl font-bold text-slate-800">Fee Setup</h1>
          <p className="text-sm text-slate-500 mt-1">Configure session tuition fees per class</p>
        </div>
      </div>

      <div className="glass-card p-6 border-l-4 border-l-orange-400 bg-orange-50/50">
        <div className="flex gap-3">
          <AlertCircle className="w-5 h-5 text-orange-500 shrink-0" />
          <div className="text-sm text-orange-800">
            <p className="font-semibold mb-1">Important Notice</p>
            <p>Setting the fee here applies to the <strong>Current Academic Session</strong>. Once set, go to <strong>Student Billing</strong> to generate invoices for the students.</p>
          </div>
        </div>
      </div>

      <div className="glass-card overflow-hidden">
        <div className="overflow-x-auto">
          <table className="w-full text-left text-sm text-slate-600">
            <thead className="bg-slate-50/50 text-xs uppercase text-slate-500 font-semibold border-b border-slate-200">
              <tr>
                <th className="px-6 py-4">Class Level</th>
                <th className="px-6 py-4">Class Name</th>
                <th className="px-6 py-4">Tuition Amount (NGN)</th>
                <th className="px-6 py-4 text-right">Actions</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100">
              {loading ? (
                <tr>
                  <td colSpan="4" className="px-6 py-8 text-center">Loading...</td>
                </tr>
              ) : classes.length === 0 ? (
                <tr>
                  <td colSpan="4" className="px-6 py-8 text-center">No classes found.</td>
                </tr>
              ) : (
                classes.map((cls, idx) => (
                  <motion.tr 
                    initial={{ opacity: 0, y: 10 }}
                    animate={{ opacity: 1, y: 0 }}
                    transition={{ delay: idx * 0.05 }}
                    key={cls.class_id}
                    className="hover:bg-slate-50/50"
                  >
                    <td className="px-6 py-4 font-medium">{cls.level}</td>
                    <td className="px-6 py-4">{cls.class_name}</td>
                    <td className="px-6 py-4 w-48">
                      <input 
                        type="number" 
                        value={cls.amount}
                        onChange={(e) => handleAmountChange(idx, e.target.value)}
                        className="w-full glass-input text-right font-mono"
                      />
                    </td>
                    <td className="px-6 py-4 text-right">
                      <button 
                        onClick={() => handleSave(cls.class_id, cls.amount)}
                        className="glass-button text-xs py-1.5 px-3 flex items-center gap-1 ml-auto"
                      >
                        <Save className="w-3 h-3" /> Save
                      </button>
                    </td>
                  </motion.tr>
                ))
              )}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
};

export default FeeSetup;
