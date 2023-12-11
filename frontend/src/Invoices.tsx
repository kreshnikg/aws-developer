import React, {useEffect, useState} from 'react';
import {Link} from "react-router-dom";
import InvoiceService, {Invoice} from "./services/InvoiceService";

function Invoices() {

    const [invoices, setInvoices] = useState<Invoice[]>([]);

    const getInvoices = () => {
        InvoiceService.getInvoices()
            .then((response) => {
                setInvoices(response.data)
            }).catch((error) => {

        });
    }

    const download = (id: number) => {
        InvoiceService.download(id)
            .then((response) => {
                window.open(response.data)
            }).catch((error) => {

        });
    }

    const sendEmail = (id: number) => {
        InvoiceService.sendEmail(id)
            .then((response) => {

            }).catch((error) => {

        });
    }

    const sendEmailAsync = (id: number) => {
        InvoiceService.sendEmailAsync(id)
            .then((response) => {

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
                    <th scope="col"/>
                </tr>
                </thead>
                <tbody>
                {invoices.map((invoice, index) => {
                    return (
                        <tr key={index}>
                            <th scope="row">{invoice.id}</th>
                            <td>{invoice.client}</td>
                            <td>{invoice.amount} E</td>
                            <td>
                                <Link to={`/invoices/${invoice.id}`}>Edit </Link>
                                <a href="#" onClick={() => download(invoice.id)}>Download</a>
                                <a href="#" onClick={() => sendEmail(invoice.id)}> SendEmail</a>
                                <a href="#" onClick={() => sendEmailAsync(invoice.id)}> SendEmailAsync</a>
                            </td>
                        </tr>
                    )
                })}
                </tbody>
            </table>
        </>
    );
}

export default Invoices;
