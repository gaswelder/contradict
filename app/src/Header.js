import React from "react";
import { withRouter } from "react-router";
import { Link } from "react-router-dom";
import api from "./api";

const Header = withRouter(function Header(props) {
  async function logout() {
    try {
      await api.logout();
      props.history.push("/login");
    } catch (err) {
      alert("failed to log out: " + err);
    }
  }
  return (
    <>
      <Link to="/">Dict</Link>
      <button className="logout" onClick={logout}>
        Logout
      </button>
    </>
  );
});

export default Header;
