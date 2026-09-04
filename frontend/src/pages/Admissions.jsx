import React, { useState, useEffect } from 'react';
import { motion } from 'framer-motion';
import { Inbox, CheckCircle, XCircle, Search, UserCheck } from 'lucide-react';
import api from '../api';
import Modal from '../components/ui/Modal';

const Admissions = () => {
  const [apps, setApps] = useState([]);
  const [classes, setClasses] = useState([]);
  const [loading, setLoading] = useState(true);
  const [searchTerm, setSearchTerm] = useState('');
  
  // Approve Modal State
  const [isApproveOpen, setIsApproveOpen] = useState(false);
  const [selectedApp, setSelectedApp] = useState(null);
  const [selectedClassId, setSelectedClassId] = useState('');
  const [processing, setProcessing] = useState(false);

  useEffect(() => {
    fetchData();
  }, []);

  const fetchData = async () => {
    try {
      const [appRes, classRes] = await Promise.all([
        api.get(`/admissions?t=${Date.now()}`),
        api.get(`/classes?t=${Date.now()}`)
      ]);
      setApps(Array.isArray(appRes.data) ? appRes.data : []);
      // /api/classes returns { classes: [...], teachers: [...] }
      setClasses(classRes.data && Array.isArray(classRes.data.classes) ? classRes.data.classes : []);
    } catch (err) {
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  const handleOpenApprove = (app) => {
    setSelectedApp(app);
    setSelectedClassId(app.desired_class_id); // default to what they applied for
    setIsApproveOpen(true);
  };

  const handleApprove = async (e) => {
    e.preventDefault();
    setProcessing(true);
    try {
      await api.post(`/admissions/${selectedApp.id}/approve`, { class_id: selectedClassId });
      // Optimistic UI Update: instantly remove from list
      setApps(apps.filter(app => app.id !== selectedApp.id));
      alert('Application Approved and Student Enrolled!');
      setIsApproveOpen(false);
      // Still fetch in background to sync
      fetchData();
    } catch (err) {
      alert(err.response?.data?.error || 'Failed to approve');
    } finally {
      setProcessing(false);
    }
  };

  const handleReject = async (id) => {
    if (!await window.confirmAction("Are you sure you want to reject this application?")) return;
    try {
      await api.post(`/admissions/${id}/reject`);
      // Optimistic UI Update
      setApps(apps.filter(app => app.id !== id));
      // Sync
      fetchData();
    } catch (err) {
      alert('Failed to reject');
    }
  };

  const filtered = apps.filter(a => 
    (a.first_name || '').toLowerCase().includes(searchTerm.toLowerCase()) || 
    (a.surname || '').toLowerCase().includes(searchTerm.toLowerCase()) ||
    (a.guardian_phone || '').includes(searchTerm)
  );

  return (
    <div className="space-y-6 max-w-6xl mx-auto">
      <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div className="flex items-center gap-3">
          <div className="w-10 h-10 rounded-xl bg-royal-blue/10 flex items-center justify-center text-royal-blue">
            <Inbox className="w-6 h-6" />
          </div>
          <div>
            <h1 className="text-2xl font-bold text-slate-800">Admission Applications</h1>
            <p className="text-sm text-slate-500 mt-1">Review and manage new student enrollments</p>
          </div>
        </div>
        <div className="relative w-full sm:w-64">
          <Search className="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
          <input 
            type="text" 
            placeholder="Search applicants..." 
            value={searchTerm}
            onChange={e => setSearchTerm(e.target.value)}
            className="w-full pl-9 pr-4 py-2 glass-input"
          />
        </div>
      </div>

      <div className="glass-card overflow-hidden">
        <div className="overflow-x-auto">
          <table className="w-full text-left text-sm text-slate-600 min-w-[900px]">
            <thead className="bg-slate-50/50 text-xs uppercase text-slate-500 font-semibold border-b border-slate-200">
              <tr>
                <th className="px-6 py-4">Date</th>
                <th className="px-6 py-4">Applicant Name</th>
                <th className="px-6 py-4">Gender & DOB</th>
                <th className="px-6 py-4">Desired Class</th>
                <th className="px-6 py-4">Guardian Contact</th>
                <th className="px-6 py-4">Status</th>
                <th className="px-6 py-4 text-right">Actions</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100">
              {loading ? (
                <tr><td colSpan="7" className="p-8 text-center">Loading...</td></tr>
              ) : filtered.length === 0 ? (
                <tr><td colSpan="7" className="p-8 text-center text-slate-400">No applications found.</td></tr>
              ) : (
                filtered.map((app, idx) => (
                  <motion.tr 
                    initial={{ opacity: 0, y: 10 }}
                    animate={{ opacity: 1, y: 0 }}
                    transition={{ delay: idx * 0.02 }}
                    key={app.id} className="hover:bg-slate-50/50"
                  >
                    <td className="px-6 py-4 font-mono text-xs">{new Date(app.application_date).toLocaleDateString()}</td>
                    <td className="px-6 py-4">
                      <div className="font-bold text-slate-800">{app.first_name} {app.surname}</div>
                      <div className="text-xs text-slate-500">{app.middle_name}</div>
                    </td>
                    <td className="px-6 py-4">
                      <div>{app.gender}</div>
                      <div className="text-xs text-slate-500 font-mono">{app.date_of_birth}</div>
                    </td>
                    <td className="px-6 py-4 font-medium text-royal-blue">{app.desired_class_name}</td>
                    <td className="px-6 py-4">
                      <div className="font-medium">{app.guardian_name}</div>
                      <div className="text-xs text-slate-500">{app.guardian_phone}</div>
                    </td>
                    <td className="px-6 py-4">
                      {app.status === 'pending' && <span className="px-2 py-1 bg-orange-100 text-orange-700 rounded text-xs font-bold uppercase">Pending</span>}
                      {app.status === 'approved' && <span className="px-2 py-1 bg-green-100 text-green-700 rounded text-xs font-bold uppercase">Approved</span>}
                      {app.status === 'rejected' && <span className="px-2 py-1 bg-red-100 text-red-700 rounded text-xs font-bold uppercase">Rejected</span>}
                    </td>
                    <td className="px-6 py-4 text-right space-x-2">
                      {app.status === 'pending' && (
                        <>
                          <button onClick={() => handleOpenApprove(app)} className="p-1.5 bg-green-50 text-green-600 hover:bg-green-100 rounded-lg" title="Approve">
                            <CheckCircle className="w-4 h-4" />
                          </button>
                          <button onClick={() => handleReject(app.id)} className="p-1.5 bg-red-50 text-red-600 hover:bg-red-100 rounded-lg" title="Reject">
                            <XCircle className="w-4 h-4" />
                          </button>
                        </>
                      )}
                    </td>
                  </motion.tr>
                ))
              )}
            </tbody>
          </table>
        </div>
      </div>

      {/* Approval Modal */}
      <Modal isOpen={isApproveOpen} onClose={() => setIsApproveOpen(false)} title="Approve & Enroll Student">
        {selectedApp && (
          <form onSubmit={handleApprove} className="space-y-4">
            <div className="bg-slate-50 p-4 rounded-lg border border-slate-100 flex gap-4 items-center">
              <div className="w-12 h-12 rounded-full bg-royal-blue/10 flex items-center justify-center text-royal-blue shrink-0">
                <UserCheck className="w-6 h-6" />
              </div>
              <div>
                <p className="font-bold text-slate-800">{selectedApp.first_name} {selectedApp.surname}</p>
                <p className="text-xs text-slate-500">Gender: {selectedApp.gender} &bull; DOB: {selectedApp.date_of_birth}</p>
              </div>
            </div>

            <div>
              <label className="block text-sm font-medium text-slate-700 mb-1">Enroll in Class *</label>
              <select 
                required
                value={selectedClassId}
                onChange={e => setSelectedClassId(e.target.value)}
                className="w-full glass-input"
              >
                <option value="">-- Select Class --</option>
                {classes.map(c => (
                  <option key={c.id} value={c.id}>{c.name} ({c.level})</option>
                ))}
              </select>
              <p className="text-xs text-slate-500 mt-1">
                Applicant applied for: <strong>{selectedApp.desired_class_name}</strong>
              </p>
            </div>

            <div className="pt-4 flex justify-end gap-2 border-t border-slate-100">
              <button type="button" onClick={() => setIsApproveOpen(false)} className="px-4 py-2 text-sm text-slate-600 hover:bg-slate-100 rounded-lg">Cancel</button>
              <button type="submit" disabled={processing} className="glass-button flex items-center gap-2">
                {processing ? 'Enrolling...' : 'Confirm Enrollment'}
              </button>
            </div>
          </form>
        )}
      </Modal>

    </div>
  );
};

export default Admissions;
