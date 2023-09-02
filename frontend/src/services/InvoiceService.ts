import Service from "./Service";
import {AxiosPromise} from "axios";

type InvoiceRequest = {
    client: string;
    amount: string;
}

class InvoiceService extends Service {
    getInvoices(): AxiosPromise {
        return this.axiosInstance.get('/invoices');
    }

    create(data: InvoiceRequest): AxiosPromise {
        return this.axiosInstance.post('/invoices', data)
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