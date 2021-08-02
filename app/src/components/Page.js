import React from "react";
import styled from "styled-components";
import Header from "../Header";

const HeaderContainer = styled.header`
  background-color: #204c72;
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

export const Page = ({ children, header }) => {
  return (
    <>
      {header && (
        <HeaderContainer>
          <Header />
        </HeaderContainer>
      )}
      <Content>{children}</Content>
    </>
  );
};
