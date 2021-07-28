import React from "react";
import { withRouter } from "react-router";
import Resource from "../components/Resource";
import withAPI from "../components/withAPI";
import { TestSection } from "./Test";

const TestPage = ({ api, match, busy, history }) => {
  const handleSubmit = async (entries) => {
    const id = match.params.id;
    const results = await api.submitAnswers(id, entries);
    localStorage.setItem(`results-${id}`, JSON.stringify(results));
    history.push(`results`);
  };
  return (
    <Resource getPromise={() => api.test(match.params.id)}>
      {(data) => (
        <form
          method="post"
          className="test-form"
          onSubmit={(e) => {
            e.preventDefault();
            const r = [...e.target.querySelectorAll("input")].map((input) => [
              input.name,
              input.value,
            ]);
            handleSubmit(r);
          }}
        >
          <TestSection tuples={data.tuples1} dir="0" />
          <TestSection tuples={data.tuples2} dir="1" />
          <div>
            <button type="submit" disabled={busy}>
              Submit
            </button>
          </div>
        </form>
      )}
    </Resource>
  );
};

export default withRouter(withAPI(TestPage));
