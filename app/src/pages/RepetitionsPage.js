import React, { useEffect, useState } from "react";
import { withRouter } from "react-router";
import styled from "styled-components";
import withAPI from "../components/withAPI";
import { Card } from "./Card";

const ContainerDiv = styled.div`
  text-align: center;
`;

const shuffle = (xs) =>
  xs
    .map((x) => [Math.random(), x])
    .sort((a, b) => a[0] - b[0])
    .map((x) => x[1]);

export const RepetitionsPage = withRouter(
  withAPI(({ api, match, busy }) => {
    const dictID = match.params.id;
    const [cards, setCards] = useState([]);
    const [show, setShow] = useState(false);
    const [yes, setYes] = useState(false);

    const nextBatch = async () => {
      const r = await api.test(dictID);
      setCards(
        shuffle([...r.tuples1, ...r.tuples2.slice(0, r.tuples2.legth / 3)])
      );
    };

    const next = async () => {
      setShow(false);
      setYes(false);
      if (cards.length == 1) {
        await nextBatch();
      } else {
        setCards(cards.slice(1));
      }
    };

    useEffect(() => {
      nextBatch();
    }, []);

    const card = cards[0];
    if (!card) {
      return "loading";
    }
    return (
      <ContainerDiv>
        <Card
          card={card}
          show={show}
          onShow={() => {
            if (show) {
              return;
            }
            setShow(true);
            api.touchCard(dictID, card.id, card.reverse, false);
          }}
          onChange={(newContent) => {
            api.updateEntry(card.id, { q: card.q, a: newContent });
            setCards([{ ...card, a: newContent }, ...cards.slice(1)]);
          }}
        />
        {show && (
          <>
            <button onClick={next}>Next</button>{" "}
            {yes && (
              <>
                <button
                  onClick={() => {
                    api.touchCard(dictID, card.id, card.reverse, false);
                    api.touchCard(dictID, card.id, card.reverse, false);
                    next();
                  }}
                >
                  Oops, wrong guess
                </button>
              </>
            )}
          </>
        )}
        {!show && (
          <>
            <button
              disabled={busy}
              onClick={() => {
                api.touchCard(dictID, card.id, card.reverse, true);
                setShow(true);
                setYes(true);
              }}
            >
              Yes, know it
            </button>{" "}
            <button
              onClick={async () => {
                api.touchCard(dictID, card.id, card.reverse, false);
                setShow(true);
              }}
            >
              No, forgot it
            </button>
          </>
        )}
      </ContainerDiv>
    );
  })
);
