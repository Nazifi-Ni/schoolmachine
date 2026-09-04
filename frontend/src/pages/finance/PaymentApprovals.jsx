import React, { useState, useEffect } from 'react';
import { motion } from 'framer-motion';
import { CheckCircle, XCircle, Clock, Eye, AlertCircle } from 'lucide-react';
import api from '../../api';
import Modal from '../../components/ui/Modal';

const PaymentApprovals = () => {
  const [payments, setPayments] = useState([]);
  const [loading, setLoading] = useState(true);
  const [selectedPayment, setSelectedPayment] = useState(null);
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [actionLoading, setActionLoading] = useState(false);

  const fetchPendingPayments = async () => {
    try {
      const res = await api.get('/finance/pending-approvals');
      setPayments(res.data || []);
    } catch (err) {
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchPendingPayments();
  }, []);

  const handleAction = async (paymentId, action) => {
    if (!await window.confirmAction(`Are you sure you want to ${action} this payment?`)) return;
    setActionLoading(true);
    try {
      await api.post(`/finance/approvals/${paymentId}`, { action });
      alert(`Payment ${action}d successfully`);
      setIsModalOpen(false);
      fetchPendingPayments();
    } catch (err) {
      alert(err.response?.data?.error || `Failed to ${action} payment.`);
    } finally {
      setActionLoading(false);
    }
  };

  const openModal = (payment) => {
    setSelectedPayment(payment);
    setIsModalOpen(true);
  };

  const formatMoney = (amount) => {
    return new Intl.NumberFormat('en-NG', { style: 'currency', currency: 'NGN' }).format(amount);
  };

  return (
    <div className="space-y-6">
      <div className="flex items-center gap-3 mb-6">
        <div className="w-10 h-10 rounded-xl bg-orange-50 flex items-center justify-center text-orange-600">
          <Clock className="w-6 h-6" />
        </div>
        <div>
          <h1 className="text-2xl font-bold text-slate-800">Payment Approvals</h1>
          <p className="text-sm text-slate-500 mt-1">Review and approve parent uploaded receipts</p>
        </div>
      </div>

      <div className="glass-card overflow-hidden">
        {loading ? (
          <div className="p-8 text-center text-slate-500">Loading pending payments...</div>
        ) : payments.length === 0 ? (
          <div className="p-12 text-center flex flex-col items-center justify-center">
            <CheckCircle className="w-12 h-12 text-green-300 mb-4" />
            <h3 className="text-lg font-bold text-slate-700">All Caught Up!</h3>
            <p className="text-slate-500">There are no pending payment receipts to approve.</p>
          </div>
        ) : (
          <div className="overflow-x-auto">
            <table className="w-full text-left text-sm whitespace-nowrap">
              <thead className="bg-slate-50/50 text-slate-500 border-b border-slate-100">
                <tr>
                  <th className="px-6 py-4 font-semibold">Date</th>
                  <th className="px-6 py-4 font-semibold">Student</th>
                  <th className="px-6 py-4 font-semibold">Class</th>
                  <th className="px-6 py-4 font-semibold">Reference</th>
                  <th className="px-6 py-4 font-semibold text-right">Amount</th>
                  <th className="px-6 py-4 font-semibold text-center">Action</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-100">
                {payments.map((p) => (
                  <tr key={p.id} className="hover:bg-slate-50/50 transition-colors">
                    <td className="px-6 py-4 text-slate-600">{new Date(p.payment_date).toLocaleDateString()}</td>
                    <td className="px-6 py-4">
                      <div className="font-medium text-slate-800">{p.first_name} {p.surname}</div>
                      <div className="text-xs text-slate-500">{p.registration_number}</div>
                    </td>
                    <td className="px-6 py-4 text-slate-600">{p.class_name}</td>
                    <td className="px-6 py-4 text-xs font-mono text-slate-500">{p.reference}</td>
                    <td className="px-6 py-4 text-right font-bold text-slate-800">{formatMoney(p.amount)}</td>
                    <td className="px-6 py-4 text-center">
                      <button 
                        onClick={() => openModal(p)}
                        className="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors"
                      >
                        <Eye className="w-3.5 h-3.5" /> View Receipt
                      </button>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </div>

      <Modal isOpen={isModalOpen} onClose={() => setIsModalOpen(false)} title="Review Payment">
        {selectedPayment && (
          <div className="space-y-6">
            <div className="grid grid-cols-2 gap-4">
              <div className="p-3 bg-slate-50 rounded-xl border border-slate-100">
                <p className="text-xs text-slate-500">Student</p>
                <p className="font-bold text-slate-800">{selectedPayment.first_name} {selectedPayment.surname}</p>
                <p className="text-xs text-slate-500">{selectedPayment.class_name}</p>
              </div>
              <div className="p-3 bg-slate-50 rounded-xl border border-slate-100">
                <p className="text-xs text-slate-500">Amount Sent</p>
                <p className="font-bold text-green-600 text-lg">{formatMoney(selectedPayment.amount)}</p>
              </div>
            </div>

            <div className="border border-slate-200 rounded-xl overflow-hidden bg-slate-100 min-h-[300px] flex items-center justify-center relative">
              {selectedPayment.receipt_url.endsWith('.pdf') ? (
                <a href={selectedPayment.receipt_url} target="_blank" rel="noreferrer" className="flex items-center gap-2 text-blue-600 font-bold hover:underline">
                  <Eye className="w-5 h-5"/> Click to view PDF Receipt
                </a>
              ) : (
                <img 
                  src={selectedPayment.receipt_url} 
                  alt="Payment Receipt" 
                  className="w-full h-auto object-contain max-h-[60vh]"
                />
              )}
            </div>

            <div className="flex gap-3 pt-2">
              <button 
                onClick={() => handleAction(selectedPayment.id, 'reject')}
                disabled={actionLoading}
                className="flex-1 py-3 bg-red-50 hover:bg-red-100 text-red-600 font-bold rounded-xl transition-colors disabled:opacity-50 flex items-center justify-center gap-2"
              >
                <XCircle className="w-5 h-5" /> Reject Payment
              </button>
              <button 
                onClick={() => handleAction(selectedPayment.id, 'approve')}
                disabled={actionLoading}
                className="flex-1 py-3 bg-green-500 hover:bg-green-600 text-white font-bold rounded-xl transition-colors shadow-sm shadow-green-500/20 disabled:opacity-50 flex items-center justify-center gap-2"
              >
                <CheckCircle className="w-5 h-5" /> Approve Payment
              </button>
            </div>
          </div>
        )}
      </Modal>
    </div>
  );
};

export default PaymentApprovals;
