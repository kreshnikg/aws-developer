import axios, {AxiosInstance} from "axios";

class Service {
    axiosInstance: AxiosInstance;

    constructor() {
        this.axiosInstance = axios.create({
            baseURL: process.env.REACT_APP_API_BASE_URL
        });
    }
}

export default Service;