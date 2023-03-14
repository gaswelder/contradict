import React, { useEffect, useState } from "react";
import { Link } from "react-router-dom";
import { ROOT_PATH } from "./api";
import Dictionary from "./Dictionary";
import { useAPI } from "./withAPI";

const MenuPage = () => {
  const { api } = useAPI();
  const [state, setState] = useState(null);
  const [dicts, setDicts] = useState(null);
  const load = async () => {
    setDicts(await api.dicts());
  };
  useEffect(() => {
    load();
  }, []);
  if (!dicts) {
    return "Loading";
  }
  return (
    <>
      {!dicts.length && <p>No dictionaries</p>}
      {dicts.map((d) => (
        <Dictionary key={d.id} dict={d} />
      ))}
      {state ? (
        <>
          <input
            autoFocus
            value={state.title}
            onChange={(e) => {
              setState({ ...state, title: e.target.value });
            }}
          />
          <button
            onClick={async () => {
              setState(null);
              await api.createDict(state.title);
              load();
            }}
          >
            Save
          </button>
        </>
      ) : (
        <button
          onClick={() => {
            setState({
              title: "",
            });
          }}
        >
          +
        </button>
      )}

      <p>
        <Link to={`${ROOT_PATH}export`} className="import">
          Export/Import
        </Link>
      </p>
    </>
  );
};

export default MenuPage;
