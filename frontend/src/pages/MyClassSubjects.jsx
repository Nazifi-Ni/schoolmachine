import React, { useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import api from '../api';

const MyClassSubjects = () => {
  const navigate = useNavigate();
  
  useEffect(() => {
    api.get('/my-class')
      .then(res => {
        if(res.data.class) {
          navigate('/classes/' + res.data.class.id + '/subjects', { replace: true });
        } else {
          navigate('/my-class', { replace: true });
        }
      })
      .catch(() => navigate('/my-class', { replace: true }));
  }, [navigate]);
  
  return <div className="p-8 text-center text-slate-500">Redirecting to subjects...</div>;
};

export default MyClassSubjects;
