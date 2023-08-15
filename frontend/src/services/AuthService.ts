import Service from "./Service";
import {AxiosPromise} from "axios";

type LoginRequest = {
    username: string;
    password: string;
}

class AuthService extends Service {
    login(data: LoginRequest): AxiosPromise {
        return this.axiosInstance.post('/api/login_check', data);
    }
}

export default new AuthService();
