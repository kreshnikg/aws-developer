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
}

export default new InvoiceService();