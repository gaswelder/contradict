import React from "react";
import { withRouter } from "react-router";
import styled from "styled-components";
import Resource from "../../components/Resource";
import withAPI from "../../components/withAPI";
import { TestSection } from "./Test";

const Form = styled.form`
  & > div {
    columns: 20em;
    border-bottom: 1px dashed #bebedb;
    padding-bottom: 1em;
    margin-bottom: 2em;
  }
  & > div > section {
    vertical-align: top;
    margin-bottom: 1em;
  }
  & > div > section > div {
    display: flex;
    flex-direction: column;
    margin-bottom: 0.5em;
  }
`;

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
        <Form
          method="post"
          className="test-form"
          onFocus={(e) => {
            e.target.scrollIntoView({ behavior: "smooth", block: "nearest" });
          }}
          onSubmit={(e) => {
            e.preventDefault();
            const r = [...e.target.querySelectorAll("input")].map((input) => [
              input.name,
              input.value,
            ]);
            handleSubmit(r);
          }}
        >
          <div>
            <TestSection tuples={data.tuples1} dir="0" />
          </div>
          <div>
            <TestSection tuples={data.tuples2} dir="1" />
          </div>
          <button type="submit" disabled={busy}>
            Submit
          </button>
        </Form>
      )}
    </Resource>
  );
};

export default withRouter(withAPI(TestPage));
