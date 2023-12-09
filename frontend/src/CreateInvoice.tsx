import React, {FormEvent, useRef, useState} from 'react';
import {Link} from "react-router-dom";
import InvoiceService, {Item} from "./services/InvoiceService";

function CreateInvoice() {

    const [items, setItems] = useState<Item[]>([]);

    const clientInput = useRef<HTMLInputElement>(null);
    const amountInput = useRef<HTMLInputElement>(null);

    const createInvoice = (e: FormEvent<HTMLFormElement>) => {
        e.preventDefault();
        if (!clientInput.current || !amountInput.current) return;

        InvoiceService.create({
            client: clientInput.current.value,
            amount: amountInput.current.value,
            items: items
        }).then((response) => {

        }).catch((error) => {

        });
    }

    const handleInputChange = (index: number, key: string, value: string) => {
        const newItems = [...items];
        // @ts-ignore
        newItems[index][key] = value;
        setItems(newItems);
    };

    const handleAddItem = () => {
        setItems([...items, { title: '', price: '', quantity: '' }]);
    };

    const handleRemoveItem = (index: number) => {
        const newItems = [...items];
        newItems.splice(index, 1);
        setItems(newItems);
    };

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
                <div className="container mt-5">
                    {items.map((item, index) => (
                        <div key={index} className="row mb-3">
                            <div className="col">
                                <label htmlFor={`title-${index}`} className="form-label">Title</label>
                                <input
                                    type="text"
                                    className="form-control"
                                    placeholder="Enter title"
                                    value={item.title}
                                    onChange={(e) => handleInputChange(index, 'title', e.target.value)}
                                />
                            </div>
                            <div className="col">
                                <label htmlFor={`price-${index}`} className="form-label">Price</label>
                                <input
                                    type="text"
                                    className="form-control"
                                    placeholder="Enter price"
                                    value={item.price}
                                    onChange={(e) => handleInputChange(index, 'price', e.target.value)}
                                />
                            </div>
                            <div className="col">
                                <label htmlFor={`quantity-${index}`} className="form-label">Quantity</label>
                                <input
                                    type="text"
                                    className="form-control"
                                    placeholder="Enter quantity"
                                    value={item.quantity}
                                    onChange={(e) => handleInputChange(index, 'quantity', e.target.value)}
                                />
                            </div>
                            <div className="col d-flex align-items-center">
                                {items.length > 1 && (
                                    <button type="button" className="btn btn-danger"
                                            onClick={() => handleRemoveItem(index)}>
                                        Remove
                                    </button>
                                )}
                            </div>
                        </div>
                    ))}
                    <button type="button" className="btn btn-primary" onClick={handleAddItem}>
                        Add Item
                    </button>
                </div>
                <button type="submit" className="btn btn-primary mt-5">Create</button>
            </form>
        </>
    );
}

export default CreateInvoice;
