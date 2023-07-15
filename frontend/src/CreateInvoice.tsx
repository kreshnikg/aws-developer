import React, {FormEvent, useRef} from 'react';
import axios from 'axios';
import {Link} from "react-router-dom";

function CreateInvoice() {

    const clientInput = useRef<HTMLInputElement>(null);
    const amountInput = useRef<HTMLInputElement>(null);

    const createInvoice = (e: FormEvent<HTMLFormElement>) => {
        e.preventDefault();

        axios.post('http://localhost/invoices', {
            client: clientInput.current?.value,
            amount: amountInput.current?.value
        }).then((response) => {

        }).catch((error) => {

        });
    }

    return (
        <>
            <div>
                <h5 className="mb-3"><Link to="/">&larr; Back to invoices</Link></h5>
            </div>
            <div className="d-flex align-items-center mb-3">
                <h1>Create Invoice</h1>
            </div>
            <form onSubmit={createInvoice}>
                <div className="mb-3">
                    <label htmlFor="client" className="form-label">Client</label>
                    <input type="text"
                           className="form-control"
                           ref={clientInput}
                           required
                           id="client"/>
                </div>
                <div className="mb-3">
                    <label htmlFor="amount" className="form-label">Amount</label>
                    <input type="text"
                           className="form-control"
                           ref={amountInput}
                           required
                           id="amount"/>
                </div>
                <button type="submit" className="btn btn-primary">Create</button>
            </form>
        </>
    );
}

export default CreateInvoice;
