import React from "react";
import api, { ROOT_PATH } from "./api";
import { withRouter } from "react-router";

function withAPI(Component) {
  return withRouter(
    class withAPI extends React.Component {
      constructor(props) {
        super(props);
        this.state = {
          error: null,
          busy: 0,
        };

        this.api = {};
        Object.keys(api).forEach((func) => {
          this.api[func] = async (...args) => {
            this.setState((s) => ({ busy: s.busy + 1 }));
            try {
              return await api[func](...args);
            } catch (error) {
              if (error.unauthorized) {
                this.props.history.push(ROOT_PATH + "login");
                return;
              }
              this.setState({ error });
            } finally {
              this.setState((s) => ({ busy: s.busy - 1 }));
            }
          };
        });
      }

      render() {
        if (this.state.error) {
          return this.state.error.toString();
        }
        return (
          <Component
            api={this.api}
            {...this.props}
            busy={this.state.busy > 0}
          />
        );
      }
    }
  );
}

export default withAPI;
