import React, { Fragment, useState } from "react";
import styled from "styled-components";
import { LinkButton } from "../components/LinkButton";

const CardDiv = styled.div`
  padding: 20px;
  box-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
  border-radius: 4px;
  max-width: 20em;
  border: 1px solid #eef;
  margin: 2em auto;
  background-color: ${(props) => (props.reverse ? "#ffd9e0" : "white")};
  position: relative;
  min-height: 6em;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  & .corner {
    position: absolute;
    opacity: 0.7;
    right: 10px;
    top: 10px;
    font-size: 90%;
  }
  & ul {
    padding: 0;
  }
  & li {
    font-size: 10pt;
    display: inline-block;
  }
  & li + li {
    margin-left: 0.5em;
  }
`;

const urlTitle = (url) => {
  return new URL(url).hostname
    .split(".")
    .filter((x) => x != "www" && x != "com" && x != "org")
    .join(".");
};

export const Card = ({ card, show, onShow, onChange }) => {
  const [state, setState] = useState({ editing: false, q: "", a: "" });
  return (
    <CardDiv reverse={card.reverse} onClick={onShow}>
      <div className="corner" title="score">
        {card.score}
      </div>
      <p>{card.q}</p>
      {show && (
        <>
          {state.editing ? (
            <>
              <input
                value={state.q}
                onChange={(e) => {
                  setState({ ...state, q: e.target.value });
                }}
              />
              <br />
              <textarea
                value={state.a}
                onChange={(e) => {
                  setState({ ...state, a: e.target.value });
                }}
              />
              <LinkButton
                onClick={() => {
                  onChange({ ...card, q: state.q, a: state.a });
                  setState({ ...state, editing: false });
                }}
              >
                Save
              </LinkButton>
            </>
          ) : (
            <>
              {card.a.split("\n").map((line, i) => (
                <Fragment key={i}>
                  {line}
                  <br />
                </Fragment>
              ))}
              <LinkButton
                onClick={() => {
                  setState({ editing: true, q: card.q, a: card.a });
                }}
              >
                Edit
              </LinkButton>
            </>
          )}
          <ul>
            {card.urls.map((url) => (
              <li key={url}>
                <a target="_blank" rel="noreferrer" href={url}>
                  {urlTitle(url)}
                </a>
              </li>
            ))}
          </ul>
        </>
      )}
    </CardDiv>
  );
};
