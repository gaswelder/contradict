import React from "react";
import api from "./api";
import { withRouter } from "react-router";

class LoginPage extends React.Component {
  constructor(props) {
    super(props);
    this.handleSubmit = this.handleSubmit.bind(this);
  }

  async handleSubmit(e) {
    e.preventDefault();
    const password = e.target.querySelector("input").value;
    try {
      await api.login(password);
      this.props.history.push("/");
    } catch (e) {
      alert(e.toString());
    }
  }

  render() {
    return (
      <form method="post" id="login-form" onSubmit={this.handleSubmit}>
        <div>
          <input type="password" name="password" autoFocus />
        </div>
        <button type="submit">Login</button>
      </form>
    );
  }
}

export default withRouter(LoginPage);
