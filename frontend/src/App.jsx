import React from 'react';
import { BrowserRouter, Routes, Route } from 'react-router-dom';
import Home from './pages/Home';
import NewReport from './pages/NewReport';

export default function App(){
  return (
    <BrowserRouter>
      <Routes>
        <Route path="/" element={<Home/>}/>
        <Route path="/reportar" element={<NewReport/>}/>
      </Routes>
    </BrowserRouter>
  );
}
