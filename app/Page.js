import React from "react";
import { Link, useHistory } from "react-router-dom";
import styled from "styled-components";
import api, { ROOT_PATH } from "./api";

const HeaderContainer = styled.header`
  background-color: rgb(28, 100, 162);
  color: white;
  display: flex;
  align-items: center;
  padding: 0.5em 1em;

  & a {
    text-decoration: none;
    color: white;
    font-weight: bold;
    margin-right: 1em;
  }

  & .logout {
    margin-left: auto;
  }
`;

const Content = styled.main`
  flex: 1;
  overflow-y: scroll;
  padding: 1em;
`;

export const Page = ({ children, header, back }) => {
  const history = useHistory();
  async function logout() {
    try {
      await api.logout();
      history.push(ROOT_PATH + "login");
    } catch (err) {
      alert("failed to log out: " + err);
    }
  }

  return (
    <>
      {header && (
        <HeaderContainer>
          {back || <Link to={ROOT_PATH}>〈</Link>}
          <button className="logout" onClick={logout}>
            Logout
          </button>
        </HeaderContainer>
      )}
      <Content>{children}</Content>
    </>
  );
};
