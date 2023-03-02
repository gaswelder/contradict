import React from "react";
import api, { ROOT_PATH } from "./api";
import { withRouter } from "react-router";
import styled from "styled-components";

const Form = styled.form`
  height: 100%;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
`;

const LoginPage = ({ history }) => {
  const handleSubmit = async (e) => {
    e.preventDefault();
    const form = e.target;
    const login = form.querySelector('[name="login"]').value;
    const password = form.querySelector('[name="password"]').value;
    try {
      await api.login(login, password);
      history.push(ROOT_PATH);
    } catch (e) {
      alert(e.toString());
    }
  };

  return (
    <Form method="post" id="login-form" onSubmit={handleSubmit}>
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
};

export default withRouter(LoginPage);
