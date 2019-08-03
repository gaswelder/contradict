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
    const form = e.target;
    const login = form.querySelector('[name="login"]').value;
    const password = form.querySelector('[name="password"]').value;
    try {
      await api.login(login, password);
      this.props.history.push("/");
    } catch (e) {
      alert(e.toString());
    }
  }

  render() {
    return (
      <form method="post" id="login-form" onSubmit={this.handleSubmit}>
        <div>
          <input name="login" autoFocus required />
        </div>
        <div>
          <input type="password" name="password" required />
        </div>
        <button type="submit">Login</button>
      </form>
    );
  }
}

export default withRouter(LoginPage);
