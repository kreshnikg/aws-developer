import axios, {AxiosInstance} from "axios";
import TokenManager from "../TokenManager";

class Service {
    axiosInstance: AxiosInstance;

    constructor() {
        const token = TokenManager.getToken();

        this.axiosInstance = axios.create({
            baseURL: process.env.REACT_APP_API_BASE_URL,
            headers: {
                'Authorization': token ? 'Bearer ' + token : undefined
            }
        });
    }
}

export default Service;