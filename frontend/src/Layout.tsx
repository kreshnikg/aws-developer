import React, {PropsWithChildren} from 'react';
import {Link} from "react-router-dom";
import AuthService from "./services/AuthService";
import TokenManager from "./TokenManager";

const Layout: React.FC<PropsWithChildren> = (props) => {

    const logout = () => {
        AuthService.logout();
        window.location.href = "/";
    }

    return (
        <div>
            <header className="p-3 text-bg-dark mb-5">
                <div className="container">
                    <div className="d-flex flex-wrap align-items-center justify-content-center justify-content-lg-start">
                        <Link to="/" className="d-flex align-items-center mb-2 mb-lg-0 text-white text-decoration-none">InvoiceApp</Link>

                        <ul className="nav col-12 col-lg-auto me-lg-auto mb-2 justify-content-center mb-md-0 ms-5">
                            {/*<li><a href="#" className="nav-link px-2 text-secondary">Home</a></li>*/}
                        </ul>

                        <div className="text-end">
                        {TokenManager.getToken() === null ? (
                            <>
                                <Link to={'/login'} className="btn btn-outline-light me-2">Sign in</Link>
                                <button type="button" className="btn btn-primary">Sign up</button>
                            </>
                        ): (
                            <button type="button" onClick={logout} className="btn btn-outline-light">Sign out</button>
                        )}
                        </div>
                    </div>
                </div>
            </header>
            <div className="container">
                {props.children}
            </div>
        </div>
    );
}

export default Layout;
