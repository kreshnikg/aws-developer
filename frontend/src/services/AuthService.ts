import Service from "./Service";
import {AxiosPromise, AxiosResponse} from "axios";
import TokenManager from "../TokenManager";

type LoginRequest = {
    username: string;
    password: string;
}

class AuthService extends Service {
    login(data: LoginRequest): Promise<any> {
        return this.axiosInstance.post('/login', data).then((response) => {
            TokenManager.storeToken(response.data.token);
        });
    }

    logout(): void {
        TokenManager.clearToken();
    }
}

export default new AuthService();
