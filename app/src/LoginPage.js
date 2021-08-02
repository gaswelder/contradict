import React from "react";
import api from "./api";
import { withRouter } from "react-router";
import styled from "styled-components";

const Form = styled.form`
  height: 100%;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
`;

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
      <Form method="post" id="login-form" onSubmit={this.handleSubmit}>
        <div>
          <input name="login" autoFocus required placeholder="Login" />
        </div>
        <div>
          <input
            type="password"
            name="password"
            required
            placeholder="Password"
          />
        </div>
        <button type="submit">Login</button>
      </Form>
    );
  }
}

export default withRouter(LoginPage);
