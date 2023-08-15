import React from 'react';
import {BrowserRouter, Route, Routes} from "react-router-dom";
import Invoices from "./Invoices";
import CreateInvoice from "./CreateInvoice";
import Layout from "./Layout";
import Login from "./Login";

function App() {
  return (
      <BrowserRouter>
        <Layout>
            <Routes>
                <Route path="/" element={<Invoices />}/>
                <Route path="/create-invoice" element={<CreateInvoice />}/>
                <Route path="/login" element={<Login />}/>
            </Routes>
        </Layout>
      </BrowserRouter>
  );
}

export default App;
