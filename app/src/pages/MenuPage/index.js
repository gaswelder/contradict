import React from "react";
import Dictionary from "./Dictionary";
import Resource from "../../components/Resource";
import withAPI from "../../components/withAPI";

const MenuPage = ({ api }) => {
  return (
    <Resource getPromise={api.dicts}>
      {(data) => {
        if (!data.length) {
          return <p>No dictionaries.</p>;
        }
        return data.map((d) => <Dictionary key={d.id} dict={d} />);
      }}
    </Resource>
  );
};

export default withAPI(MenuPage);
