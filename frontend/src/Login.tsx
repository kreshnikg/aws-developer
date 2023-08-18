import React, {FormEvent, useRef} from 'react';
import "./style.css";
import AuthService from "./services/AuthService";

const Login = () => {

    const emailInput = useRef<HTMLInputElement>(null);
    const passwordInput = useRef<HTMLInputElement>(null);

    const login = (e: FormEvent) => {
        e.preventDefault();

        if (!emailInput.current || !passwordInput.current) return;

        AuthService.login({
            username: emailInput.current.value,
            password: passwordInput.current.value
        }).then(() => {
            window.location.href = "/";
        }).catch(() => {

        });
    }

    return (
        <div className="form-signin w-100 m-auto text-center">
            <form onSubmit={login}>
                <img className="mb-4" src="logo192.png" alt="" width="72" height="72"/>
                <h1 className="h3 mb-3 fw-normal">Please sign in</h1>
                <div className="form-floating">
                    <input type="email"
                           ref={emailInput}
                           className="form-control"
                           id="floatingInput"
                           placeholder="name@example.com"/>
                    <label htmlFor="floatingInput">Email address</label>
                </div>
                <div className="form-floating">
                    <input type="password"
                           ref={passwordInput}
                           className="form-control mb-3"
                           id="floatingPassword"
                           placeholder="Password"/>
                    <label htmlFor="floatingPassword">Password</label>
                </div>
                <button className="btn btn-primary w-100 py-2" type="submit">Sign in</button>
            </form>
        </div>
    );
}

export default Login;