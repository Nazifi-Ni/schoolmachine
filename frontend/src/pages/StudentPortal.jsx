import React, { useState, useEffect } from 'react';
import { motion } from 'framer-motion';
import { FileText, Wallet, BookOpen, User, LogOut, Award, AlertCircle } from 'lucide-react';
import api from '../api';
import { useNavigate } from 'react-router-dom';
import WhatsAppWidget from '../components/ui/WhatsAppWidget';
import Modal from '../components/ui/Modal';

const StudentPortal = () => {
  const [data, setData] = useState(null);
  const [profile, setProfile] = useState(null);
  
  // Modal states
  const [isPaymentModalOpen, setIsPaymentModalOpen] = useState(false);
  const [receiptFile, setReceiptFile] = useState(null);
  const [paymentAmount, setPaymentAmount] = useState(0);

  const [current_session, setCurrentSession] = useState(null);
  const [fee, setFee] = useState(null);
  const [results, setResults] = useState([]);
  const [loading, setLoading] = useState(true);
  
  const [selectedResult, setSelectedResult] = useState(null);
  const [isResultModalOpen, setIsResultModalOpen] = useState(false);
  
  const navigate = useNavigate();

  useEffect(() => {
    fetchData();
  }, []);

  const fetchData = async () => {
    try {
      const res = await api.get('/student-portal/dashboard');
      setProfile(res.data.profile);
      setCurrentSession(res.data.current_session);
      setFee(res.data.fee);
      setResults(res.data.results);
      setData(res.data);
    } catch (err) {
      if (err.response?.status === 403 || err.response?.status === 401) {
        navigate('/student-login');
      }
    } finally {
      setLoading(false);
    }
  };

  const openPaymentModal = (amountDue) => {
    setPaymentAmount(amountDue);
    setReceiptFile(null);
    setIsPaymentModalOpen(true);
  };

  const submitReceipt = async (e) => {
    e.preventDefault();
    if (!receiptFile) {
      alert("Please select a receipt image to upload.");
      return;
    }

    const formData = new FormData();
    formData.append('receipt', receiptFile);
    formData.append('amount', paymentAmount);

    try {
      setLoading(true);
      const res = await api.post('/student-portal/pay/submit-receipt', formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      });
      alert(res.data.message || 'Receipt submitted successfully!');
      setIsPaymentModalOpen(false);
      fetchData(); // refresh dashboard
    } catch (err) {
      alert(err.response?.data?.error || 'Failed to submit receipt.');
      setLoading(false);
    }
  };

  const handleLogout = async () => {
    try {
      await api.post('/logout');
      navigate('/student-login');
    } catch (err) {
      console.error(err);
    }
  };

  const viewResult = async (id) => {
    try {
      const res = await api.get(`/student-portal/results/${id}`);
      setSelectedResult(res.data);
      setIsResultModalOpen(true);
    } catch (err) {
      alert('Failed to fetch result details.');
    }
  };

  if (loading || !data || !profile) {
    return <div className="min-h-screen bg-slate-50 flex items-center justify-center">Loading Portal...</div>;
  }

  return (
    <div className="min-h-screen bg-slate-50 pb-20">
      
      {/* Top Navbar */}
      <nav className="bg-white/70 backdrop-blur-xl shadow-[0_4px_30px_rgba(0,0,0,0.05)] border-b border-white sticky top-0 z-50">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex justify-between h-16 items-center">
            <div className="flex items-center gap-3">
              <div className="w-10 h-10 bg-white rounded-lg p-1 shadow-sm border border-slate-100">
                 <img src="/assets/images/school_logo.jpg" alt="Logo" className="w-full h-full object-contain" onError={(e) => e.target.src='https://via.placeholder.com/32'} />
              </div>
              <div>
                <h1 className="font-bold text-lg leading-tight tracking-tight text-royal-blue">IAMS ARMS</h1>
                <p className="text-[10px] text-slate-500 uppercase tracking-widest font-semibold">Student Portal</p>
              </div>
            </div>
            <button onClick={handleLogout} className="flex items-center gap-2 text-sm text-red-500 hover:text-red-700 hover:bg-red-50 transition-colors bg-white px-4 py-2 rounded-full border border-red-100 shadow-sm font-medium">
              <LogOut className="w-4 h-4" />
              <span className="hidden sm:inline">Logout</span>
            </button>
          </div>
        </div>
      </nav>

      {/* Main Content */}
      <main className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        
        {/* Welcome Card */}
        <motion.div 
          initial={{ y: 10, opacity: 0 }}
          animate={{ y: 0, opacity: 1 }}
          className="bg-white rounded-3xl shadow-sm border border-slate-100 p-6 sm:p-8 flex flex-col sm:flex-row items-center sm:items-start gap-6"
        >
          <div className="w-24 h-24 bg-slate-100 rounded-full flex items-center justify-center text-slate-400 shrink-0 border-4 border-white shadow-md">
            <User className="w-10 h-10" />
          </div>
          <div className="text-center sm:text-left flex-1">
            <h2 className="text-2xl font-bold text-slate-800">Welcome, {profile.first_name} {profile.surname}</h2>
            <div className="mt-2 flex flex-wrap justify-center sm:justify-start gap-3 text-sm">
              <span className="inline-flex items-center gap-1.5 px-3 py-1 bg-blue-50 text-royal-blue font-medium rounded-full border border-blue-100">
                <FileText className="w-4 h-4" />
                {profile.registration_number}
              </span>
              <span className="inline-flex items-center gap-1.5 px-3 py-1 bg-purple-50 text-purple-700 font-medium rounded-full border border-purple-100">
                <BookOpen className="w-4 h-4" />
                {profile.class_name}
              </span>
            </div>
          </div>
        </motion.div>

        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
          
          {/* Finance Overview (1/3 width) */}
          <motion.div 
            initial={{ y: 10, opacity: 0 }}
            animate={{ y: 0, opacity: 1 }}
            transition={{ delay: 0.1 }}
            className="lg:col-span-1 bg-white rounded-3xl shadow-sm border border-slate-100 p-6"
          >
            <div className="flex items-center gap-3 mb-6">
              <div className="w-10 h-10 rounded-xl bg-green-50 flex items-center justify-center text-green-600">
                <Wallet className="w-5 h-5" />
              </div>
              <h3 className="font-bold text-slate-800 text-lg">Financial Status</h3>
            </div>

            {fee ? (
              <div className="space-y-4">
                <div className="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                  <p className="text-xs text-slate-500 uppercase font-bold tracking-wider mb-1">Session</p>
                  <p className="font-semibold text-slate-700">{current_session?.name}</p>
                </div>
                
                <div className="grid grid-cols-2 gap-4">
                  <div className="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                    <p className="text-xs text-slate-500 mb-1">Total Bill</p>
                    <p className="font-bold text-slate-800">₦{Number(fee.total_amount).toLocaleString()}</p>
                  </div>
                  <div className="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                    <p className="text-xs text-slate-500 mb-1">Amount Paid</p>
                    <p className="font-bold text-green-600">₦{Number(fee.paid_amount).toLocaleString()}</p>
                  </div>
                </div>

                <div className={`p-4 rounded-2xl border flex flex-col gap-3 ${
                  fee.status === 'paid' ? 'bg-green-50 border-green-200' : 
                  fee.status === 'partial' ? 'bg-orange-50 border-orange-200' : 'bg-red-50 border-red-200'
                }`}>
                  <div className="flex items-center justify-between">
                    <div>
                      <p className={`text-xs mb-0.5 ${
                        fee.status === 'paid' ? 'text-green-600' : 
                        fee.status === 'partial' ? 'text-orange-600' : 'text-red-600'
                      }`}>Balance Due</p>
                      <p className={`font-bold text-lg ${
                        fee.status === 'paid' ? 'text-green-700' : 
                        fee.status === 'partial' ? 'text-orange-700' : 'text-red-700'
                      }`}>₦{(Number(fee.total_amount) - Number(fee.paid_amount)).toLocaleString()}</p>
                    </div>
                    <span className={`px-2.5 py-1 text-xs font-bold uppercase rounded-md ${
                      fee.status === 'paid' ? 'bg-green-200 text-green-800' : 
                      fee.status === 'partial' ? 'bg-orange-200 text-orange-800' : 'bg-red-200 text-red-800'
                    }`}>
                      {fee.status}
                    </span>
                  </div>
                  
                  {fee.status !== 'paid' && fee.status !== 'pending' && (
                    <button 
                      onClick={() => openPaymentModal(Number(fee.total_amount) - Number(fee.paid_amount))}
                      className="w-full mt-2 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl transition-colors flex items-center justify-center gap-2"
                    >
                      <Wallet className="w-4 h-4" /> I want to Pay
                    </button>
                  )}
                  {fee.status === 'pending' && (
                    <div className="w-full mt-2 py-2.5 bg-yellow-100 text-yellow-800 text-sm font-bold rounded-xl text-center">
                      Payment Verification Pending
                    </div>
                  )}
                </div>
              </div>
            ) : (
              <div className="text-center py-8 text-slate-500 flex flex-col items-center">
                <AlertCircle className="w-8 h-8 text-slate-300 mb-2" />
                <p>No billing records found for the current session.</p>
              </div>
            )}
          </motion.div>

          {/* Academic Results (2/3 width) */}
          <motion.div 
            initial={{ y: 10, opacity: 0 }}
            animate={{ y: 0, opacity: 1 }}
            transition={{ delay: 0.2 }}
            className="lg:col-span-2 bg-white rounded-3xl shadow-sm border border-slate-100 p-6"
          >
            <div className="flex items-center gap-3 mb-6">
              <div className="w-10 h-10 rounded-xl bg-purple-50 flex items-center justify-center text-purple-600">
                <Award className="w-5 h-5" />
              </div>
              <h3 className="font-bold text-slate-800 text-lg">Academic Reports</h3>
            </div>

            {results.length === 0 ? (
              <div className="text-center py-12 text-slate-500 bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                <p>No academic results available yet.</p>
              </div>
            ) : (
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {results.map((res, idx) => (
                  <div key={res.id} className="p-5 rounded-2xl border border-slate-200 hover:border-royal-blue/30 hover:shadow-md transition-all bg-white group cursor-pointer" onClick={() => viewResult(res.id)}>
                    <div className="flex justify-between items-start mb-4">
                      <div>
                        <h4 className="font-bold text-slate-800">{res.term_name}</h4>
                        <p className="text-xs text-slate-500">{res.session_name} &bull; {res.class_name}</p>
                      </div>
                      <span className="w-8 h-8 rounded-full bg-slate-50 flex items-center justify-center text-royal-blue group-hover:bg-royal-blue group-hover:text-white transition-colors">
                        <FileText className="w-4 h-4" />
                      </span>
                    </div>
                    <div className="flex items-center justify-between pt-4 border-t border-slate-100">
                      <div>
                        <p className="text-[10px] text-slate-400 uppercase font-bold tracking-wider">Average</p>
                        <p className="font-bold text-slate-700">{res.average_score}%</p>
                      </div>
                      <div className="text-right">
                        <p className="text-[10px] text-slate-400 uppercase font-bold tracking-wider">Position</p>
                        <p className="font-bold text-royal-blue">{res.position}{
                          res.position === 1 ? 'st' : res.position === 2 ? 'nd' : res.position === 3 ? 'rd' : 'th'
                        }</p>
                      </div>
                    </div>
                  </div>
                ))}
              </div>
            )}
          </motion.div>

        </div>
      </main>

      <WhatsAppWidget />

      {/* Result Modal */}
      <Modal isOpen={isResultModalOpen} onClose={() => setIsResultModalOpen(false)} title="Term Result Details" size="3xl">
        <style>{`
          @media print {
            body * { visibility: hidden; }
            .print-section, .print-section * { visibility: visible; }
            .print-section { position: absolute; left: 0; top: 0; width: 100%; }
            .no-print { display: none !important; }
          }
        `}</style>
        {selectedResult && (
          <div className="space-y-6 print-section">
            <div className="flex justify-between items-center bg-slate-50 p-4 rounded-xl border border-slate-100">
              <div>
                <h4 className="font-bold text-slate-800">{selectedResult.result.term_name}, {selectedResult.result.session_name}</h4>
                <p className="text-sm text-slate-500">Class: {selectedResult.result.class_name}</p>
              </div>
              <div className="text-right">
                <div className="text-2xl font-black text-royal-blue">
                  {selectedResult.result.average_score}%
                </div>
                <p className="text-xs text-slate-500 font-medium">Class Average</p>
              </div>
            </div>

            <div className="overflow-x-auto border border-slate-200 rounded-xl">
              <table className="w-full text-left text-sm">
                <thead className="bg-slate-50 text-xs uppercase text-slate-500">
                  <tr>
                    <th className="px-4 py-3 border-b">Subject</th>
                    <th className="px-4 py-3 border-b text-center">CA 1 (20)</th>
                    <th className="px-4 py-3 border-b text-center">CA 2 (20)</th>
                    <th className="px-4 py-3 border-b text-center">Exam (60)</th>
                    <th className="px-4 py-3 border-b text-center font-bold">Total (100)</th>
                    <th className="px-4 py-3 border-b text-center">Grade</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-slate-100">
                  {selectedResult.items.map(item => (
                    <tr key={item.id} className="hover:bg-slate-50/50">
                      <td className="px-4 py-3 font-medium text-slate-700">{item.subject_name}</td>
                      <td className="px-4 py-3 text-center">{item.ca1_score}</td>
                      <td className="px-4 py-3 text-center">{item.ca2_score}</td>
                      <td className="px-4 py-3 text-center">{item.exam_score}</td>
                      <td className="px-4 py-3 text-center font-bold text-royal-blue">{item.total_score}</td>
                      <td className="px-4 py-3 text-center font-bold">{item.grade}</td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>

            <div className="flex justify-between items-center p-4 bg-blue-50 rounded-xl text-royal-blue border border-blue-100">
               <div>
                 <p className="text-xs uppercase font-bold tracking-wider opacity-80 mb-0.5">Overall Position</p>
                 <p className="text-lg font-black">{selectedResult.result.position} / {selectedResult.total_students}</p>
               </div>
               <button onClick={() => window.print()} className="no-print px-4 py-2 bg-royal-blue text-white text-sm font-bold rounded-lg shadow-sm hover:bg-blue-700 transition-colors">
                 Print Report Card
               </button>
            </div>
          </div>
        )}
      </Modal>
      <Modal isOpen={isPaymentModalOpen} onClose={() => setIsPaymentModalOpen(false)} title="Upload Payment Receipt">
        <form onSubmit={submitReceipt} className="space-y-6">
          {data?.school_info && (
            <div className="bg-blue-50 border border-blue-100 p-4 rounded-xl mb-4">
              <h4 className="font-bold text-royal-blue mb-2">School Bank Details</h4>
              <p className="text-sm text-slate-700"><strong>Bank Name:</strong> {data.school_info.bank_name}</p>
              <p className="text-sm text-slate-700"><strong>Account Name:</strong> {data.school_info.account_name}</p>
              <p className="text-sm text-slate-700"><strong>Account Number:</strong> <span className="font-mono text-lg">{data.school_info.account_number}</span></p>
              <p className="text-sm mt-3 text-slate-600">Please transfer exactly <strong>₦{paymentAmount.toLocaleString()}</strong> to the account above and upload the receipt.</p>
            </div>
          )}

          <div>
            <label className="block text-sm font-medium text-slate-700 mb-2">Select Receipt Image (JPG, PNG, PDF)</label>
            <input 
              type="file" 
              accept=".jpg,.jpeg,.png,.pdf" 
              onChange={(e) => setReceiptFile(e.target.files[0])}
              className="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:border-royal-blue focus:ring-1 focus:ring-royal-blue outline-none" 
              required
            />
          </div>

          <div className="flex gap-3 justify-end pt-4 border-t border-slate-100">
            <button type="button" onClick={() => setIsPaymentModalOpen(false)} className="px-5 py-2.5 text-sm font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">
              Cancel
            </button>
            <button type="submit" disabled={loading || !receiptFile} className="px-5 py-2.5 text-sm font-bold text-white bg-royal-blue hover:bg-blue-700 rounded-xl shadow-sm transition-colors disabled:opacity-50">
              {loading ? 'Submitting...' : 'I have sent the money'}
            </button>
          </div>
        </form>
      </Modal>

    </div>
  );
};

export default StudentPortal;
