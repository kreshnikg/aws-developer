import Service from "./Service";
import {AxiosPromise} from "axios";

export type Item = {
    title: string;
    price: string;
    quantity: string;
}

export type Invoice = {
    id: number;
    client: string;
    amount: string;
    items: Item[];
}

export type InvoiceRequest = {
    client: string;
    amount: string;
    items: Item[];
}

class InvoiceService extends Service {
    getInvoices(): AxiosPromise {
        return this.axiosInstance.get('/invoices');
    }

    create(data: InvoiceRequest): AxiosPromise {
        return this.axiosInstance.post('/invoices', data)
    }

    update(id: number, data: InvoiceRequest): AxiosPromise {
        return this.axiosInstance.put(`/invoices/${id}`, data)
    }

    getInvoice(id: string): AxiosPromise {
        return this.axiosInstance.get(`/invoices/${id}`);
    }

    download(id: number): AxiosPromise {
        return this.axiosInstance.get(`/invoices/${id}/download`);
    }

    sendEmail(id: number): AxiosPromise {
        return this.axiosInstance.post(`/invoices/${id}/send-email`);
    }

    sendEmailAsync(id: number): AxiosPromise {
        return this.axiosInstance.post(`/invoices/${id}/send-email-async`);
    }
}

export default new InvoiceService();