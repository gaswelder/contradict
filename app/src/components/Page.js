import React from "react";
import Header from "../Header";

export const Page = ({ children, header }) => {
  return (
    <main className="page">
      {header && <Header />}
      {children}
    </main>
  );
};
