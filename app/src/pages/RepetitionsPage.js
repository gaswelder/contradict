import React, { useEffect, useState } from "react";
import { withRouter } from "react-router";
import styled from "styled-components";
import withAPI from "../components/withAPI";

const ContainerDiv = styled.div`
  text-align: center;
`;

const Card = styled.div`
  padding: 40px;
  box-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
  border-radius: 4px;
  max-width: 20em;
  border: 1px solid #eef;
  margin: 2em auto;
  background-color: ${(props) => (props.reverse ? "#ffd9e0" : "white")};
`;

const shuffle = (xs) =>
  xs
    .map((x) => [Math.random(), x])
    .sort((a, b) => a[0] - b[0])
    .map((x) => x[1]);

export const RepetitionsPage = withRouter(
  withAPI(({ api, match, busy }) => {
    const dictId = match.params.id;
    const [cards, setCards] = useState([]);
    const [show, setShow] = useState(false);

    const nextBatch = async () => {
      const r = await api.test(dictId);
      setCards(shuffle([...r.tuples1, ...r.tuples2]));
    };

    const next = async () => {
      if (cards.length == 1) {
        await nextBatch();
      } else {
        setCards(cards.slice(1));
      }
    };

    useEffect(() => {
      nextBatch();
    }, []);

    const cc = cards[0];
    if (!cc) {
      return "loading";
    }
    return (
      <ContainerDiv>
        <Card reverse={cc.reverse}>
          {cc.q} ({cc.hint})
          {show && (
            <p>
              {cc.a} <a href={cc.wikiURL}>wiki</a>
            </p>
          )}
        </Card>
        {!show && (
          <button
            disabled={busy}
            onClick={async () => {
              api.touchCard(cc.id, cc.reverse, true);
              next();
            }}
          >
            Yes, know it
          </button>
        )}{" "}
        <button
          onClick={async () => {
            if (!show) {
              setShow(true);
              api.touchCard(cc.id, cc.reverse, true);
            } else {
              next();
              setShow(false);
            }
          }}
        >
          {show ? "Next" : "No, forgot it"}
        </button>
      </ContainerDiv>
    );
  })
);
