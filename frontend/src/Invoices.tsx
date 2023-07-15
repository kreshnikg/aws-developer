import React, {useEffect, useState} from 'react';
import {Link} from "react-router-dom";
import axios from 'axios';

type Invoice = {
    id: number;
    client: string;
    amount: string;
}

function Invoices() {

    const [invoices, setInvoices] = useState<Invoice[]>([]);

    const getInvoices = () => {
        axios.get('http://localhost/invoices')
            .then((response) => {
                setInvoices(response.data)
            }).catch((error) => {

        });
    }

    useEffect(() => {
        getInvoices();
    }, [])

    return (
        <>
            <div className="d-flex align-items-center">
                <h1>Invoices</h1>
                <Link to="/create-invoice" className="btn btn-primary ms-auto">
                    Create Invoice
                </Link>
            </div>
            <table className="table">
                <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Client</th>
                    <th scope="col">Amount</th>
                </tr>
                </thead>
                <tbody>
                {invoices.map((invoice, index) => {
                    return (
                        <tr key={index}>
                            <th scope="row">{invoice.id}</th>
                            <td>{invoice.client}</td>
                            <td>{invoice.amount} E</td>
                        </tr>
                    )
                })}
                </tbody>
            </table>
        </>
    );
}

export default Invoices;
