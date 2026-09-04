import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { ArrowLeft, Plus, CheckCircle, CreditCard, Banknote, Calendar, Receipt } from 'lucide-react';
import api from '../../api';
import Modal from '../../components/ui/Modal';

const StudentFinance = () => {
  const { id } = useParams();
  const navigate = useNavigate();
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [payData, setPayData] = useState({ amount: '', method: 'Cash', reference: '' });
  const [processing, setProcessing] = useState(false);

  useEffect(() => {
    fetchStudentFinance();
  }, [id]);

  const fetchStudentFinance = async () => {
    try {
      const res = await api.get(`/finance/student/${id}`);
      setData(res.data);
    } catch (err) {
      alert(err.response?.data?.error || 'Failed to fetch student finance');
      navigate('/finance/bills');
    } finally {
      setLoading(false);
    }
  };

  const handlePayment = async (e) => {
    e.preventDefault();
    setProcessing(true);
    try {
      await api.post('/finance/pay', {
        student_fee_id: data.fee.id,
        amount: payData.amount,
        payment_method: payData.method,
        reference_number: payData.reference
      });
      alert('Payment recorded successfully!');
      setIsModalOpen(false);
      setPayData({ amount: '', method: 'Cash', reference: '' });
      fetchStudentFinance();
    } catch (err) {
      alert('Failed to record payment');
    } finally {
      setProcessing(false);
    }
  };

  if (loading) return <div className="p-8 text-center text-slate-500">Loading finance data...</div>;
  if (!data) return null;

  const { fee, payments } = data;
  const balance = parseFloat(fee.total_amount) - parseFloat(fee.paid_amount);

  const formatMoney = (amount) => {
    return new Intl.NumberFormat('en-NG', { style: 'currency', currency: 'NGN' }).format(amount);
  };

  return (
    <div className="space-y-6 max-w-5xl mx-auto">
      <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div className="flex items-center gap-4">
          <button 
            onClick={() => navigate('/finance/bills')}
            className="p-2 bg-white/60 border border-slate-200 rounded-lg hover:bg-slate-50 text-slate-500 transition-colors"
          >
            <ArrowLeft className="w-5 h-5" />
          </button>
          <div>
            <h1 className="text-2xl font-bold text-slate-800">
              {fee.first_name} {fee.surname}
            </h1>
            <p className="text-sm text-slate-500 font-mono mt-1">{fee.registration_number} &bull; {fee.class_name}</p>
          </div>
        </div>
        
        {fee.status !== 'paid' && (
          <button 
            onClick={() => setIsModalOpen(true)}
            className="glass-button flex items-center gap-2"
          >
            <Plus className="w-4 h-4" /> Record Payment
          </button>
        )}
      </div>

      <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
        {/* Invoice Summary */}
        <div className="md:col-span-1 space-y-6">
          <div className="glass-card p-6 border-t-4 border-t-royal-blue">
            <h3 className="font-bold text-slate-800 mb-4 flex items-center gap-2">
              <Receipt className="w-5 h-5 text-royal-blue" />
              Session Invoice
            </h3>
            
            <div className="space-y-4">
              <div className="flex justify-between items-center pb-4 border-b border-slate-100">
                <span className="text-slate-500 text-sm">Status</span>
                {fee.status === 'paid' ? (
                  <span className="px-2.5 py-1 rounded-md text-xs font-bold bg-green-100 text-green-700">PAID IN FULL</span>
                ) : (
                  <span className={`px-2.5 py-1 rounded-md text-xs font-bold ${fee.status === 'partial' ? 'bg-orange-100 text-orange-700' : 'bg-red-100 text-red-700'}`}>
                    {fee.status.toUpperCase()}
                  </span>
                )}
              </div>
              
              <div className="flex justify-between items-center">
                <span className="text-slate-600 font-medium">Total Tuition</span>
                <span className="font-mono font-bold text-slate-800">{formatMoney(fee.total_amount)}</span>
              </div>
              
              <div className="flex justify-between items-center text-green-600">
                <span className="font-medium">Total Paid</span>
                <span className="font-mono font-bold">-{formatMoney(fee.paid_amount)}</span>
              </div>
              
              <div className="flex justify-between items-center pt-4 border-t border-slate-200 mt-2">
                <span className="font-bold text-slate-800">Balance Due</span>
                <span className="font-mono font-bold text-xl text-red-600">{formatMoney(balance)}</span>
              </div>
            </div>
          </div>
        </div>

        {/* Payment History */}
        <div className="md:col-span-2">
          <div className="glass-card overflow-hidden h-full flex flex-col">
            <div className="p-4 border-b border-slate-200 bg-white/50">
              <h3 className="font-bold text-slate-800 flex items-center gap-2">
                <CreditCard className="w-5 h-5 text-royal-blue" />
                Payment History
              </h3>
            </div>
            
            <div className="flex-1 overflow-x-auto">
              <table className="w-full text-left text-sm text-slate-600 min-w-[500px]">
                <thead className="bg-slate-50/50 text-xs uppercase text-slate-500 font-semibold border-b border-slate-200">
                  <tr>
                    <th className="px-6 py-4">Date</th>
                    <th className="px-6 py-4">Method</th>
                    <th className="px-6 py-4">Ref/Teller</th>
                    <th className="px-6 py-4 text-right">Amount</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-slate-100">
                  {payments.length === 0 ? (
                    <tr>
                      <td colSpan="4" className="px-6 py-12 text-center text-slate-400">
                        No payments recorded yet.
                      </td>
                    </tr>
                  ) : (
                    payments.map(p => (
                      <tr key={p.id} className="hover:bg-slate-50/50">
                        <td className="px-6 py-4">
                          <div className="flex items-center gap-2">
                            <Calendar className="w-4 h-4 text-slate-400" />
                            {new Date(p.payment_date).toLocaleDateString()}
                          </div>
                        </td>
                        <td className="px-6 py-4">
                          <span className="inline-flex items-center gap-1.5 px-2 py-1 rounded bg-slate-100 text-slate-700 text-xs font-medium">
                            {p.payment_method === 'Cash' ? <Banknote className="w-3 h-3" /> : <CreditCard className="w-3 h-3" />}
                            {p.payment_method}
                          </span>
                        </td>
                        <td className="px-6 py-4 font-mono text-xs">{p.reference_number || '-'}</td>
                        <td className="px-6 py-4 text-right font-mono font-bold text-green-600">
                          {formatMoney(p.amount_paid)}
                        </td>
                      </tr>
                    ))
                  )}
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <Modal isOpen={isModalOpen} onClose={() => setIsModalOpen(false)} title="Record Payment">
        <form onSubmit={handlePayment} className="space-y-4">
          <div>
            <label className="block text-xs font-medium text-slate-600 mb-1">Amount Paid (NGN)</label>
            <input 
              type="number" 
              required 
              max={balance}
              value={payData.amount} 
              onChange={e => setPayData({...payData, amount: e.target.value})} 
              className="w-full glass-input text-lg font-mono" 
              placeholder={`Max: ${balance}`}
            />
          </div>
          
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label className="block text-xs font-medium text-slate-600 mb-1">Payment Method</label>
              <select 
                required 
                value={payData.method} 
                onChange={e => setPayData({...payData, method: e.target.value})} 
                className="w-full glass-input"
              >
                <option value="Cash">Cash</option>
                <option value="Bank Transfer">Bank Transfer</option>
                <option value="POS">POS</option>
                <option value="Bank Deposit">Bank Deposit</option>
              </select>
            </div>
            <div>
              <label className="block text-xs font-medium text-slate-600 mb-1">Reference / Teller No (Optional)</label>
              <input 
                type="text" 
                value={payData.reference} 
                onChange={e => setPayData({...payData, reference: e.target.value})} 
                className="w-full glass-input" 
                placeholder="e.g. TRF-123456"
              />
            </div>
          </div>

          <div className="pt-4 flex justify-end gap-2">
            <button type="button" onClick={() => setIsModalOpen(false)} className="px-4 py-2 text-sm text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">Cancel</button>
            <button type="submit" disabled={processing} className="glass-button flex items-center gap-2">
              <CheckCircle className="w-4 h-4" /> {processing ? 'Recording...' : 'Record Payment'}
            </button>
          </div>
        </form>
      </Modal>

    </div>
  );
};

export default StudentFinance;
