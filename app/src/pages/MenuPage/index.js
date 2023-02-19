import React from "react";
import Dictionary from "./Dictionary";
import Resource from "../../components/Resource";
import withAPI from "../../components/withAPI";
import { Link } from "react-router-dom";
import { ROOT_PATH } from "../../api";

const MenuPage = ({ api }) => {
  return (
    <>
      <Resource getPromise={api.dicts}>
        {(data) => {
          if (!data.length) {
            return <p>No dictionaries.</p>;
          }
          return data.map((d) => <Dictionary key={d.id} dict={d} />);
        }}
      </Resource>
      <Link to={`${ROOT_PATH}export`} className="import">
        Export/Import
      </Link>
    </>
  );
};

export default withAPI(MenuPage);
